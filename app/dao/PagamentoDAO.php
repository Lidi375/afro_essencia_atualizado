<?php

/**
 * PagamentoDAO — acesso a dados da tabela pagamentos.
 *
 * Métodos existentes preservados sem alteração:
 *   create(), findByAgendamento(), receitaPorMetodo()
 *
 * Novos métodos adicionados:
 *   createPendente(), confirmar(), estornar(),
 *   findById(), findByCliente(), findAll(),
 *   totalPorPeriodo(), countPendentesByCliente()
 */
class PagamentoDAO extends BaseDAO
{
    // =========================================================
    // EXISTENTE — não alterado
    // =========================================================

    /**
     * Cria pagamento com status 'pago' imediato (fluxo admin existente).
     */
    public function create(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO pagamentos
                (agendamento_id, metodo_pagamento, valor, status_pagamento, data_pagamento)
             VALUES (?, ?, ?, 'pago', NOW())"
        );
        return $stmt->execute([
            $dados['agendamento_id'],
            $dados['metodo'],
            $dados['valor'],
        ]);
    }

    /**
     * Busca pagamento pelo ID do agendamento.
     */
    public function findByAgendamento(int $agendamentoId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM pagamentos WHERE agendamento_id = ? LIMIT 1"
        );
        $stmt->execute([$agendamentoId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Receita agrupada por método — usada no dashboard admin.
     */
    public function receitaPorMetodo(): array
    {
        return $this->conn->query(
            "SELECT metodo_pagamento AS metodo,
                    COUNT(*)         AS quantidade,
                    SUM(valor)       AS total
             FROM pagamentos
             WHERE status_pagamento = 'pago'
               AND MONTH(data_pagamento) = MONTH(CURDATE())
               AND YEAR(data_pagamento)  = YEAR(CURDATE())
             GROUP BY metodo_pagamento
             ORDER BY total DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    // =========================================================
    // NOVOS — fluxo de pagamento automatizado
    // =========================================================

    /**
     * Cria pagamento com status 'pendente' (cliente inicia o pagamento).
     * Retorna o ID gerado para rastreamento.
     */
    public function createPendente(array $dados): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO pagamentos
                (agendamento_id, metodo_pagamento, valor, status_pagamento)
             VALUES (?, ?, ?, 'pendente')"
        );
        $stmt->execute([
            $dados['agendamento_id'],
            $dados['metodo'],
            $dados['valor'],
        ]);
        return (int) $this->conn->lastInsertId();
    }

    /**
     * Confirma pagamento pendente (muda para 'pago' e registra data).
     */
    public function confirmar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE pagamentos
             SET status_pagamento = 'pago',
                 data_pagamento   = NOW()
             WHERE id = ?
               AND status_pagamento = 'pendente'"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Estorna um pagamento confirmado.
     */
    public function estornar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE pagamentos
             SET status_pagamento = 'estornado'
             WHERE id = ?
               AND status_pagamento = 'pago'"
        );
        return $stmt->execute([$id]);
    }

    /**
     * Busca pagamento por ID.
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.*,
                    a.data_agendamento,
                    a.hora_inicio,
                    s.nome  AS servico,
                    u.nome  AS cliente_nome
             FROM pagamentos p
             JOIN agendamentos a ON p.agendamento_id = a.id
             JOIN servicos      s ON a.servico_id     = s.id
             JOIN clientes      c ON a.cliente_id     = c.id
             JOIN usuarios      u ON c.usuario_id     = u.id
             WHERE p.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Lista pagamentos de um cliente (via usuario_id).
     */
    public function findByCliente(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.id,
                    p.metodo_pagamento,
                    p.valor,
                    p.status_pagamento,
                    p.data_pagamento,
                    p.created_at,
                    a.data_agendamento,
                    a.hora_inicio,
                    s.nome AS servico,
                    pu.nome AS profissional
             FROM pagamentos p
             JOIN agendamentos a  ON p.agendamento_id  = a.id
             JOIN servicos      s  ON a.servico_id      = s.id
             JOIN clientes      c  ON a.cliente_id      = c.id
             JOIN profissionais pr ON a.profissional_id = pr.id
             JOIN usuarios      pu ON pr.usuario_id     = pu.id
             WHERE c.usuario_id = ?
             ORDER BY p.created_at DESC"
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todos os pagamentos (admin) com dados enriquecidos.
     */
    public function findAll(): array
    {
        return $this->conn->query(
            "SELECT p.id,
                    p.metodo_pagamento,
                    p.valor,
                    p.status_pagamento,
                    p.data_pagamento,
                    p.created_at,
                    a.id   AS agendamento_id,
                    a.data_agendamento,
                    a.hora_inicio,
                    s.nome AS servico,
                    u.nome AS cliente_nome,
                    pu.nome AS profissional
             FROM pagamentos p
             JOIN agendamentos a  ON p.agendamento_id  = a.id
             JOIN servicos      s  ON a.servico_id      = s.id
             JOIN clientes      c  ON a.cliente_id      = c.id
             JOIN usuarios      u  ON c.usuario_id      = u.id
             JOIN profissionais pr ON a.profissional_id = pr.id
             JOIN usuarios      pu ON pr.usuario_id     = pu.id
             ORDER BY p.created_at DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Total de pagamentos confirmados em um intervalo de datas.
     */
    public function totalPorPeriodo(string $dataInicio, string $dataFim): float
    {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(valor), 0)
             FROM pagamentos
             WHERE status_pagamento = 'pago'
               AND DATE(data_pagamento) BETWEEN ? AND ?"
        );
        $stmt->execute([$dataInicio, $dataFim]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Quantidade de pagamentos pendentes de um cliente (para alertas).
     */
    public function countPendentesByCliente(int $usuarioId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM pagamentos p
             JOIN agendamentos a ON p.agendamento_id = a.id
             JOIN clientes      c ON a.cliente_id     = c.id
             WHERE c.usuario_id       = ?
               AND p.status_pagamento = 'pendente'"
        );
        $stmt->execute([$usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cancela (remove) um pagamento pendente quando o agendamento é cancelado.
     */
    public function cancelarPendente(int $agendamentoId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE pagamentos
             SET status_pagamento = 'estornado'
             WHERE agendamento_id  = ?
               AND status_pagamento = 'pendente'"
        );
        return $stmt->execute([$agendamentoId]);
    }
}
