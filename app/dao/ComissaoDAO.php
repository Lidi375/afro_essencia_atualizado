<?php

/**
 * ComissaoDAO — acesso a dados das tabelas comissoes e config_comissoes.
 *
 * Todos os métodos usam Prepared Statements (segurança contra SQL Injection).
 * Nenhuma tabela existente é modificada.
 */
class ComissaoDAO extends BaseDAO
{
    // ═══════════════════════════════════════════════════════
    // CONFIG_COMISSOES
    // ═══════════════════════════════════════════════════════

    /**
     * Busca a configuração ativa de comissão de um profissional.
     */
    public function findConfigAtiva(int $profissionalId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT cc.*, u.nome AS profissional_nome
             FROM config_comissoes cc
             JOIN profissionais pr ON cc.profissional_id = pr.id
             JOIN usuarios      u  ON pr.usuario_id      = u.id
             WHERE cc.profissional_id = ?
               AND cc.ativo = 1
             ORDER BY cc.updated_at DESC
             LIMIT 1"
        );
        $stmt->execute([$profissionalId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Lista todas as configurações (para painel admin).
     */
    public function findAllConfigs(): array
    {
        return $this->conn->query(
            "SELECT cc.*,
                    u.nome  AS profissional_nome,
                    u.email AS profissional_email
             FROM config_comissoes cc
             JOIN profissionais pr ON cc.profissional_id = pr.id
             JOIN usuarios      u  ON pr.usuario_id      = u.id
             ORDER BY u.nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Salva (insert ou update) a config de comissão de um profissional.
     * Desativa configs anteriores e cria nova — mantém histórico.
     */
    public function salvarConfig(array $dados, int $adminId): int
    {
        // Desativa configuração anterior
        $stmt = $this->conn->prepare(
            "UPDATE config_comissoes
             SET ativo = 0, updated_by = ?
             WHERE profissional_id = ? AND ativo = 1"
        );
        $stmt->execute([$adminId, $dados['profissional_id']]);

        // Insere nova configuração
        $stmt = $this->conn->prepare(
            "INSERT INTO config_comissoes
                (profissional_id, tipo, percentual, valor_fixo, ativo, observacoes, created_by, updated_by)
             VALUES (?, ?, ?, ?, 1, ?, ?, ?)"
        );
        $stmt->execute([
            $dados['profissional_id'],
            $dados['tipo'],
            $dados['percentual']  ?? null,
            $dados['valor_fixo']  ?? null,
            $dados['observacoes'] ?? null,
            $adminId,
            $adminId,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    // ═══════════════════════════════════════════════════════
    // COMISSOES
    // ═══════════════════════════════════════════════════════

    /**
     * Verifica se já existe comissão para um agendamento (idempotência).
     */
    public function existeParaAgendamento(int $agendamentoId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM comissoes WHERE agendamento_id = ?"
        );
        $stmt->execute([$agendamentoId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cria uma nova comissão.
     */
    public function create(array $dados): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO comissoes
                (profissional_id, agendamento_id, config_id,
                 valor_servico, tipo_comissao, percentual, valor_comissao,
                 status_pagamento, data_geracao, observacoes)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', CURDATE(), ?)"
        );
        $stmt->execute([
            $dados['profissional_id'],
            $dados['agendamento_id'],
            $dados['config_id']     ?? null,
            $dados['valor_servico'],
            $dados['tipo_comissao'],
            $dados['percentual']    ?? null,
            $dados['valor_comissao'],
            $dados['observacoes']   ?? null,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Busca comissão por ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*,
                    u.nome  AS profissional_nome,
                    s.nome  AS servico_nome,
                    a.data_agendamento,
                    a.hora_inicio,
                    uc.nome AS cliente_nome
             FROM comissoes c
             JOIN profissionais pr ON c.profissional_id = pr.id
             JOIN usuarios      u  ON pr.usuario_id     = u.id
             JOIN agendamentos  a  ON c.agendamento_id  = a.id
             JOIN servicos      s  ON a.servico_id      = s.id
             JOIN clientes      cl ON a.cliente_id      = cl.id
             JOIN usuarios      uc ON cl.usuario_id     = uc.id
             WHERE c.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Lista comissões de um profissional com filtros opcionais.
     * Protege contra IDOR — sempre filtra por profissional_id.
     */
    public function findByProfissional(int $profissionalId, array $filtros = []): array
    {
        $where  = ["c.profissional_id = ?"];
        $params = [$profissionalId];

        if (!empty($filtros['data_inicio'])) {
            $where[]  = "c.data_geracao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "c.data_geracao <= ?";
            $params[] = $filtros['data_fim'];
        }
        if (!empty($filtros['status'])) {
            $where[]  = "c.status_pagamento = ?";
            $params[] = $filtros['status'];
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT c.*,
                    s.nome AS servico_nome,
                    a.data_agendamento,
                    a.hora_inicio,
                    uc.nome AS cliente_nome
             FROM comissoes c
             JOIN agendamentos  a  ON c.agendamento_id = a.id
             JOIN servicos      s  ON a.servico_id     = s.id
             JOIN clientes      cl ON a.cliente_id     = cl.id
             JOIN usuarios      uc ON cl.usuario_id    = uc.id
             WHERE {$whereStr}
             ORDER BY c.data_geracao DESC, c.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas as comissões (admin) com filtros opcionais.
     */
    public function findAll(array $filtros = []): array
    {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filtros['profissional_id'])) {
            $where[]  = "c.profissional_id = ?";
            $params[] = (int) $filtros['profissional_id'];
        }
        if (!empty($filtros['data_inicio'])) {
            $where[]  = "c.data_geracao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "c.data_geracao <= ?";
            $params[] = $filtros['data_fim'];
        }
        if (!empty($filtros['status'])) {
            $where[]  = "c.status_pagamento = ?";
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['forma_pagamento'])) {
            $where[]  = "c.forma_pagamento = ?";
            $params[] = $filtros['forma_pagamento'];
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT c.*,
                    u.nome  AS profissional_nome,
                    s.nome  AS servico_nome,
                    a.data_agendamento,
                    a.hora_inicio,
                    uc.nome AS cliente_nome
             FROM comissoes c
             JOIN profissionais pr ON c.profissional_id = pr.id
             JOIN usuarios      u  ON pr.usuario_id     = u.id
             JOIN agendamentos  a  ON c.agendamento_id  = a.id
             JOIN servicos      s  ON a.servico_id      = s.id
             JOIN clientes      cl ON a.cliente_id      = cl.id
             JOIN usuarios      uc ON cl.usuario_id     = uc.id
             WHERE {$whereStr}
             ORDER BY c.data_geracao DESC, c.id DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marca comissão como paga.
     */
    public function marcarPago(int $id, string $formaPagamento, int $adminId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE comissoes
             SET status_pagamento = 'pago',
                 forma_pagamento  = ?,
                 data_pagamento   = CURDATE(),
                 pago_por         = ?
             WHERE id = ?
               AND status_pagamento = 'pendente'"
        );
        return $stmt->execute([$formaPagamento, $adminId, $id]);
    }

    /**
     * Cancela uma comissão pendente.
     */
    public function cancelar(int $id, int $adminId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE comissoes
             SET status_pagamento = 'cancelado',
                 pago_por         = ?
             WHERE id = ?
               AND status_pagamento = 'pendente'"
        );
        return $stmt->execute([$adminId, $id]);
    }

    // ═══════════════════════════════════════════════════════
    // INDICADORES / RELATÓRIOS
    // ═══════════════════════════════════════════════════════

    /**
     * Totais financeiros para o dashboard admin.
     */
    public function totaisAdmin(array $filtros = []): array
    {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $where[]  = "c.data_geracao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "c.data_geracao <= ?";
            $params[] = $filtros['data_fim'];
        }
        if (!empty($filtros['profissional_id'])) {
            $where[]  = "c.profissional_id = ?";
            $params[] = (int) $filtros['profissional_id'];
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT
                COALESCE(SUM(c.valor_servico), 0)                                      AS receita_total,
                COALESCE(SUM(CASE WHEN c.status_pagamento='pago'     THEN c.valor_comissao ELSE 0 END), 0) AS comissoes_pagas,
                COALESCE(SUM(CASE WHEN c.status_pagamento='pendente' THEN c.valor_comissao ELSE 0 END), 0) AS comissoes_pendentes,
                COALESCE(SUM(c.valor_comissao), 0)                                     AS total_comissoes,
                COUNT(*)                                                                AS total_atendimentos,
                COALESCE(AVG(c.valor_servico), 0)                                      AS ticket_medio
             FROM comissoes c
             WHERE {$whereStr}"
        );
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $row['lucro_liquido'] = (float)$row['receita_total'] - (float)$row['total_comissoes'];
        return $row;
    }

    /**
     * Totais por profissional para o dashboard admin.
     */
    public function totaisPorProfissional(array $filtros = []): array
    {
        $where  = ["1=1"];
        $params = [];

        if (!empty($filtros['data_inicio'])) {
            $where[]  = "c.data_geracao >= ?";
            $params[] = $filtros['data_inicio'];
        }
        if (!empty($filtros['data_fim'])) {
            $where[]  = "c.data_geracao <= ?";
            $params[] = $filtros['data_fim'];
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->conn->prepare(
            "SELECT u.nome AS profissional_nome,
                    c.profissional_id,
                    COUNT(*) AS total_atendimentos,
                    COALESCE(SUM(c.valor_servico), 0)   AS receita_total,
                    COALESCE(SUM(c.valor_comissao), 0)  AS total_comissoes,
                    COALESCE(SUM(CASE WHEN c.status_pagamento='pago'     THEN c.valor_comissao ELSE 0 END), 0) AS comissoes_pagas,
                    COALESCE(SUM(CASE WHEN c.status_pagamento='pendente' THEN c.valor_comissao ELSE 0 END), 0) AS comissoes_pendentes
             FROM comissoes c
             JOIN profissionais pr ON c.profissional_id = pr.id
             JOIN usuarios      u  ON pr.usuario_id     = u.id
             WHERE {$whereStr}
             GROUP BY c.profissional_id, u.nome
             ORDER BY receita_total DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Totais para o dashboard do profissional.
     * SEGURO: filtra sempre por profissional_id.
     */
    public function totaisProfissional(int $profissionalId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                COALESCE(SUM(c.valor_comissao), 0)  AS ganhos_totais,
                COALESCE(SUM(CASE WHEN c.status_pagamento='pago'     THEN c.valor_comissao ELSE 0 END), 0) AS ganhos_recebidos,
                COALESCE(SUM(CASE WHEN c.status_pagamento='pendente' THEN c.valor_comissao ELSE 0 END), 0) AS ganhos_pendentes,
                COUNT(*)  AS total_atendimentos,
                COALESCE(SUM(CASE WHEN MONTH(c.data_geracao)=MONTH(CURDATE()) AND YEAR(c.data_geracao)=YEAR(CURDATE()) THEN c.valor_comissao ELSE 0 END), 0) AS ganhos_mes
             FROM comissoes c
             WHERE c.profissional_id = ?
               AND c.status_pagamento != 'cancelado'"
        );
        $stmt->execute([$profissionalId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Evolução mensal de comissões (últimos 6 meses) para gráficos.
     */
    public function evolucaoMensal(int $profissionalId = 0): array
    {
        $params = [];
        $extra  = '';
        if ($profissionalId > 0) {
            $extra  = "AND c.profissional_id = ?";
            $params[] = $profissionalId;
        }

        $stmt = $this->conn->prepare(
            "SELECT DATE_FORMAT(c.data_geracao, '%Y-%m') AS mes,
                    COALESCE(SUM(c.valor_servico),  0)   AS receita,
                    COALESCE(SUM(c.valor_comissao), 0)   AS comissoes
             FROM comissoes c
             WHERE c.data_geracao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
               AND c.status_pagamento != 'cancelado'
               {$extra}
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ═══════════════════════════════════════════════════════
    // LOG FINANCEIRO
    // ═══════════════════════════════════════════════════════

    /**
     * Registra uma ação no log de auditoria.
     */
    public function log(string $acao, string $entidade, ?int $entidadeId, ?int $usuarioId, array $detalhes = []): void
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO log_financeiro (acao, entidade, entidade_id, usuario_id, detalhes, ip)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $acao,
            $entidade,
            $entidadeId,
            $usuarioId,
            json_encode($detalhes, JSON_UNESCAPED_UNICODE),
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}
