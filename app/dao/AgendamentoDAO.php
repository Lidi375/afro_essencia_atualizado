<?php

/**
 * AgendamentoDAO
 * Todas as queries de agendamentos — prepared statements em 100% dos casos.
 */
class AgendamentoDAO extends BaseDAO
{
    // =========================================================
    // CRIAR
    // =========================================================

    public function create(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO agendamentos
                (cliente_id, profissional_id, servico_id,
                 data_agendamento, hora_inicio, hora_fim)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $dados['cliente_id'],
            $dados['profissional_id'],
            $dados['servico_id'],
            $dados['data_agendamento'],
            $dados['hora_inicio'],
            $dados['hora_fim'],
        ]);
    }

    // =========================================================
    // LEITURAS — CLIENTE
    // =========================================================

    public function findByClienteId(int $clienteId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                 a.id,
                 a.data_agendamento,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 a.servico_id,
                 a.profissional_id,
                 u.nome AS profissional,
                 s.nome AS servico,
                 s.valor
             FROM agendamentos a
             JOIN profissionais p ON a.profissional_id = p.id
             JOIN usuarios      u ON p.usuario_id      = u.id
             JOIN servicos      s ON a.servico_id      = s.id
             WHERE a.cliente_id = ?
               AND (a.oculto_cliente = 0 OR a.oculto_cliente IS NULL)
             ORDER BY a.data_agendamento ASC, a.hora_inicio ASC"
        );
        $stmt->execute([$clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function limparHistoricoCliente(int $clienteId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE agendamentos
             SET oculto_cliente = 1
             WHERE cliente_id = ?
               AND (
                   status = 'cancelado'
                   OR TIMESTAMP(data_agendamento, hora_fim) <= NOW()
               )"
        );
        return $stmt->execute([$clienteId]);
    }

    // =========================================================
    // LEITURAS — PROFISSIONAL
    // =========================================================

    public function findHojeByProfissionalId(int $profissionalId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                 a.id,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 a.observacoes,
                 u.nome  AS cliente_nome,
                 u.email AS cliente_email,
                 s.nome  AS servico,
                 s.valor AS valor
             FROM agendamentos a
             JOIN clientes  c ON a.cliente_id  = c.id
             JOIN usuarios  u ON c.usuario_id  = u.id
             JOIN servicos  s ON a.servico_id  = s.id
             WHERE a.profissional_id  = ?
               AND a.data_agendamento = CURDATE()
               AND a.status NOT IN ('cancelado')
             ORDER BY a.hora_inicio ASC"
        );
        $stmt->execute([$profissionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findProximosByProfissionalId(int $profissionalId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                 a.id,
                 a.data_agendamento,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 a.observacoes,
                 u.nome  AS cliente_nome,
                 u.email AS cliente_email,
                 s.nome  AS servico,
                 s.valor AS valor
             FROM agendamentos a
             JOIN clientes  c ON a.cliente_id  = c.id
             JOIN usuarios  u ON c.usuario_id  = u.id
             JOIN servicos  s ON a.servico_id  = s.id
             WHERE a.profissional_id = ?
               AND a.status NOT IN ('cancelado','concluido')
               AND TIMESTAMP(a.data_agendamento, a.hora_inicio) >= NOW()
             ORDER BY a.data_agendamento ASC, a.hora_inicio ASC"
        );
        $stmt->execute([$profissionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findHistoricoByProfissionalId(int $profissionalId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                 a.id,
                 a.data_agendamento,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 a.observacoes,
                 u.nome  AS cliente_nome,
                 u.email AS cliente_email,
                 s.nome  AS servico,
                 s.valor AS valor
             FROM agendamentos a
             JOIN clientes  c ON a.cliente_id  = c.id
             JOIN usuarios  u ON c.usuario_id  = u.id
             JOIN servicos  s ON a.servico_id  = s.id
             WHERE a.profissional_id = ?
               AND (
                   a.status IN ('concluido','cancelado')
                   OR TIMESTAMP(a.data_agendamento, a.hora_fim) < NOW()
               )
             ORDER BY a.data_agendamento DESC, a.hora_inicio DESC
             LIMIT 50"
        );
        $stmt->execute([$profissionalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function metricasProfissional(int $profissionalId): array
    {
        $stmtMes = $this->conn->prepare(
            "SELECT COUNT(*) FROM agendamentos
             WHERE profissional_id = ?
               AND status = 'concluido'
               AND MONTH(data_agendamento) = MONTH(CURDATE())
               AND YEAR(data_agendamento)  = YEAR(CURDATE())"
        );
        $stmtMes->execute([$profissionalId]);

        $stmtRec = $this->conn->prepare(
            "SELECT COALESCE(SUM(s.valor), 0)
             FROM agendamentos a
             JOIN servicos s ON a.servico_id = s.id
             WHERE a.profissional_id = ?
               AND a.status = 'concluido'
               AND MONTH(a.data_agendamento) = MONTH(CURDATE())
               AND YEAR(a.data_agendamento)  = YEAR(CURDATE())"
        );
        $stmtRec->execute([$profissionalId]);

        $stmtHoje = $this->conn->prepare(
            "SELECT COUNT(*) FROM agendamentos
             WHERE profissional_id  = ?
               AND data_agendamento = CURDATE()
               AND status NOT IN ('cancelado')"
        );
        $stmtHoje->execute([$profissionalId]);

        $stmtProx = $this->conn->prepare(
            "SELECT COUNT(*) FROM agendamentos
             WHERE profissional_id = ?
               AND status NOT IN ('cancelado','concluido')
               AND TIMESTAMP(data_agendamento, hora_inicio) > NOW()"
        );
        $stmtProx->execute([$profissionalId]);

        $stmtNota = $this->conn->prepare(
            "SELECT COALESCE(avaliacao_media, 0) FROM profissionais WHERE id = ?"
        );
        $stmtNota->execute([$profissionalId]);

        return [
            'atendimentos_mes' => (int)   $stmtMes->fetchColumn(),
            'receita_mes'      => (float) $stmtRec->fetchColumn(),
            'hoje'             => (int)   $stmtHoje->fetchColumn(),
            'proximos'         => (int)   $stmtProx->fetchColumn(),
            'nota_media'       => (float) $stmtNota->fetchColumn(),
        ];
    }

    // =========================================================
    // LEITURAS — ADMIN
    // =========================================================

    public function findAllAdmin(): array
    {
        $sql =
            "SELECT
                 a.id,
                 a.data_agendamento,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 a.servico_id,
                 u.nome  AS cliente,
                 pu.nome AS profissional,
                 s.nome  AS servico,
                 s.valor AS valor
             FROM agendamentos a
             JOIN clientes      c  ON a.cliente_id      = c.id
             JOIN usuarios      u  ON c.usuario_id      = u.id
             JOIN profissionais p  ON a.profissional_id = p.id
             JOIN usuarios      pu ON p.usuario_id      = pu.id
             JOIN servicos      s  ON a.servico_id      = s.id
             ORDER BY a.data_agendamento DESC, a.hora_inicio DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAgendamentosHoje(): array
    {
        $sql =
            "SELECT
                 a.id,
                 a.hora_inicio,
                 a.hora_fim,
                 a.status,
                 u.nome  AS cliente,
                 pu.nome AS profissional,
                 s.nome  AS servico,
                 s.valor AS valor
             FROM agendamentos a
             JOIN clientes      c  ON a.cliente_id      = c.id
             JOIN usuarios      u  ON c.usuario_id      = u.id
             JOIN profissionais p  ON a.profissional_id = p.id
             JOIN usuarios      pu ON p.usuario_id      = pu.id
             JOIN servicos      s  ON a.servico_id      = s.id
             WHERE a.data_agendamento = CURDATE()
             ORDER BY a.hora_inicio ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM agendamentos")->fetchColumn();
    }

    public function countByStatus(string $status): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM agendamentos WHERE status = ?");
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function countHoje(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE()"
        )->fetchColumn();
    }

    public function countCanceladosHoje(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE() AND status = 'cancelado'"
        )->fetchColumn();
    }

    public function countConcluidosHoje(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE() AND status = 'concluido'"
        )->fetchColumn();
    }

    public function faturamentoPorDia(int $dias = 30): array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.data_agendamento AS dia, SUM(s.valor) AS total
             FROM agendamentos a
             JOIN servicos s ON a.servico_id = s.id
             WHERE a.status = 'concluido'
               AND a.data_agendamento >= CURDATE() - INTERVAL ? DAY
             GROUP BY a.data_agendamento
             ORDER BY a.data_agendamento ASC"
        );
        $stmt->execute([$dias]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function topServicos(int $limite = 6): array
    {
        $stmt = $this->conn->prepare(
            "SELECT s.nome,
                    COUNT(a.id)  AS total,
                    SUM(s.valor) AS faturamento
             FROM agendamentos a
             JOIN servicos s ON a.servico_id = s.id
             WHERE a.status != 'cancelado'
             GROUP BY a.servico_id, s.nome
             ORDER BY total DESC
             LIMIT ?"
        );
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function faturamentoPorProfissional(): array
    {
        $sql =
            "SELECT u.nome AS profissional,
                    COUNT(a.id)  AS atendimentos,
                    SUM(s.valor) AS faturamento
             FROM agendamentos a
             JOIN profissionais p ON a.profissional_id = p.id
             JOIN usuarios      u ON p.usuario_id      = u.id
             JOIN servicos      s ON a.servico_id      = s.id
             WHERE a.status = 'concluido'
               AND MONTH(a.data_agendamento) = MONTH(CURDATE())
               AND YEAR(a.data_agendamento)  = YEAR(CURDATE())
             GROUP BY a.profissional_id, u.nome
             ORDER BY faturamento DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function receitaMesAtual(): float
    {
        return (float) $this->conn->query(
            "SELECT COALESCE(SUM(s.valor), 0)
             FROM agendamentos a
             JOIN servicos s ON a.servico_id = s.id
             WHERE a.status = 'concluido'
               AND MONTH(a.data_agendamento) = MONTH(CURDATE())
               AND YEAR(a.data_agendamento)  = YEAR(CURDATE())"
        )->fetchColumn();
    }

    public function faturamentoHoje(): float
    {
        return (float) $this->conn->query(
            "SELECT COALESCE(SUM(s.valor), 0)
             FROM agendamentos a
             JOIN servicos s ON a.servico_id = s.id
             WHERE a.data_agendamento = CURDATE()
               AND a.status != 'cancelado'"
        )->fetchColumn();
    }

    // =========================================================
    // DISPONIBILIDADE / CONFLITO
    // =========================================================

    public function findByProfissionalData(int $profissionalId, string $data): array
    {
        $stmt = $this->conn->prepare(
            "SELECT hora_inicio, hora_fim
             FROM agendamentos
             WHERE profissional_id  = ?
               AND data_agendamento = ?
               AND status != 'cancelado'"
        );
        $stmt->execute([$profissionalId, $data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeConflito(
        int $profissionalId,
        string $data,
        string $horaInicio,
        string $horaFim
    ): bool {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM agendamentos
             WHERE profissional_id  = ?
               AND data_agendamento = ?
               AND status != 'cancelado'
               AND hora_inicio < ? AND hora_fim > ?"
        );
        $stmt->execute([$profissionalId, $data, $horaFim, $horaInicio]);
        return $stmt->fetchColumn() > 0;
    }

    public function existeConflitoExceto(
        int $profissionalId,
        string $data,
        string $horaInicio,
        string $horaFim,
        int $excluirId
    ): bool {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM agendamentos
             WHERE profissional_id  = ?
               AND data_agendamento = ?
               AND status != 'cancelado'
               AND id != ?
               AND hora_inicio < ? AND hora_fim > ?"
        );
        $stmt->execute([$profissionalId, $data, $excluirId, $horaFim, $horaInicio]);
        return $stmt->fetchColumn() > 0;
    }

    public function getDuracaoServico(int $profissionalId, int $servicoId): ?int
    {
        $stmt = $this->conn->prepare(
            "SELECT duracao_minutos
             FROM servico_profissional
             WHERE profissional_id = ? AND servico_id = ?
             LIMIT 1"
        );
        $stmt->execute([$profissionalId, $servicoId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ? (int) $r['duracao_minutos'] : null;
    }

    // =========================================================
    // BUSCA INDIVIDUAL
    // =========================================================

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT a.*,
                    s.nome  AS servico,
                    s.valor AS valor,
                    u.nome  AS profissional
             FROM agendamentos  a
             LEFT JOIN servicos      s  ON a.servico_id      = s.id
             LEFT JOIN profissionais p  ON a.profissional_id = p.id
             LEFT JOIN usuarios      u  ON p.usuario_id      = u.id
             WHERE a.id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    // =========================================================
    // ALTERAÇÕES DE STATUS
    // =========================================================

    public function cancelar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE agendamentos SET status = 'cancelado' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function concluir(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE agendamentos SET status = 'concluido' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function reagendar(
        int $id,
        string $novaData,
        string $novaHoraInicio,
        string $novaHoraFim
    ): bool {
        $stmt = $this->conn->prepare(
            "UPDATE agendamentos
             SET data_agendamento = ?,
                 hora_inicio = ?,
                 hora_fim    = ?,
                 status      = 'pendente'
             WHERE id = ?"
        );
        return $stmt->execute([$novaData, $novaHoraInicio, $novaHoraFim, $id]);
    }
}
