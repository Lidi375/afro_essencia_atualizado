<?php

/**
 * PaymentTransactionDAO — acesso às tabelas:
 *   payment_transactions, payment_logs, webhook_logs
 *
 * Estas tabelas são criadas pelo script pagamento_gateway_migration.sql
 */
class PaymentTransactionDAO extends BaseDAO
{
    // ─── payment_transactions ─────────────────────────────────────────

    /**
     * Cria nova transação com status 'pending'.
     * Retorna o transaction_id gerado (UUID).
     */
    public function createTransaction(array $dados): string
    {
        $transactionId = $this->generateUuid();

        $stmt = $this->conn->prepare(
            "INSERT INTO payment_transactions
                (transaction_id, agendamento_id, gateway, amount, currency,
                 status, created_at, updated_at)
             VALUES (?, ?, ?, ?, 'BRL', 'pending', NOW(), NOW())"
        );
        $stmt->execute([
            $transactionId,
            $dados['agendamento_id'],
            $dados['gateway'],
            $dados['amount'],
        ]);

        return $transactionId;
    }

    /**
     * Atualiza gateway_reference após criação da preferência no gateway.
     */
    public function setGatewayReference(string $transactionId, string $gatewayReference): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE payment_transactions
             SET gateway_reference = ?, updated_at = NOW()
             WHERE transaction_id = ?"
        );
        return $stmt->execute([$gatewayReference, $transactionId]);
    }

    /**
     * Atualiza status da transação.
     * Previne regressão de status (approved não volta para pending).
     */
    public function updateStatus(string $transactionId, string $status, ?string $paidAt = null): bool
    {
        // Ordem de prioridade dos status (maior = mais definitivo)
        $priority = ['pending' => 1, 'rejected' => 2, 'cancelled' => 2, 'approved' => 3, 'refunded' => 4];
        $current  = $this->findByTransactionId($transactionId);

        if (!$current) return false;

        $currentPriority = $priority[$current['status']] ?? 0;
        $newPriority     = $priority[$status]            ?? 0;

        if ($newPriority < $currentPriority) {
            return false; // não regride status
        }

        $stmt = $this->conn->prepare(
            "UPDATE payment_transactions
             SET status = ?, paid_at = ?, updated_at = NOW()
             WHERE transaction_id = ?"
        );
        return $stmt->execute([$status, $paidAt, $transactionId]);
    }

    /**
     * Busca transação por transaction_id interno.
     */
    public function findByTransactionId(string $transactionId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT pt.*,
                    a.data_agendamento,
                    a.hora_inicio,
                    a.status AS status_agendamento,
                    s.nome   AS servico_nome,
                    u.nome   AS cliente_nome,
                    u.email  AS cliente_email
             FROM payment_transactions pt
             JOIN agendamentos a ON pt.agendamento_id = a.id
             JOIN servicos     s ON a.servico_id       = s.id
             JOIN clientes     c ON a.cliente_id       = c.id
             JOIN usuarios     u ON c.usuario_id       = u.id
             WHERE pt.transaction_id = ?
             LIMIT 1"
        );
        $stmt->execute([$transactionId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Busca transação ativa (pending) para um agendamento.
     */
    public function findPendingByAgendamento(int $agendamentoId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM payment_transactions
             WHERE agendamento_id = ? AND status = 'pending'
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$agendamentoId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Verifica se já existe transação aprovada para o agendamento.
     */
    public function hasApproved(int $agendamentoId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM payment_transactions
             WHERE agendamento_id = ? AND status = 'approved'"
        );
        $stmt->execute([$agendamentoId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Cancela transações pending de um agendamento (ao iniciar nova tentativa).
     */
    public function cancelPending(int $agendamentoId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE payment_transactions
             SET status = 'cancelled', updated_at = NOW()
             WHERE agendamento_id = ? AND status = 'pending'"
        );
        return $stmt->execute([$agendamentoId]);
    }

    /**
     * Lista transações de um agendamento.
     */
    public function findByAgendamento(int $agendamentoId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM payment_transactions
             WHERE agendamento_id = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([$agendamentoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista transações de um cliente.
     */
    public function findByCliente(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT pt.*,
                    a.data_agendamento,
                    a.hora_inicio,
                    s.nome   AS servico_nome,
                    pu.nome  AS profissional_nome
             FROM payment_transactions pt
             JOIN agendamentos a  ON pt.agendamento_id  = a.id
             JOIN servicos     s  ON a.servico_id        = s.id
             JOIN clientes     c  ON a.cliente_id        = c.id
             JOIN profissionais pr ON a.profissional_id  = pr.id
             JOIN usuarios     pu ON pr.usuario_id       = pu.id
             WHERE c.usuario_id = ?
             ORDER BY pt.created_at DESC"
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista todas as transações (admin) com filtros opcionais.
     */
    public function findAll(array $filtros = []): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['status'])) {
            $where[]  = 'pt.status = ?';
            $params[] = $filtros['status'];
        }

        if (!empty($filtros['gateway'])) {
            $where[]  = 'pt.gateway = ?';
            $params[] = $filtros['gateway'];
        }

        if (!empty($filtros['data_inicio'])) {
            $where[]  = 'DATE(pt.created_at) >= ?';
            $params[] = $filtros['data_inicio'];
        }

        if (!empty($filtros['data_fim'])) {
            $where[]  = 'DATE(pt.created_at) <= ?';
            $params[] = $filtros['data_fim'];
        }

        $sql = "SELECT pt.*,
                       a.data_agendamento,
                       a.hora_inicio,
                       s.nome   AS servico_nome,
                       u.nome   AS cliente_nome,
                       pu.nome  AS profissional_nome
                FROM payment_transactions pt
                JOIN agendamentos  a  ON pt.agendamento_id  = a.id
                JOIN servicos      s  ON a.servico_id        = s.id
                JOIN clientes      c  ON a.cliente_id        = c.id
                JOIN usuarios      u  ON c.usuario_id        = u.id
                JOIN profissionais pr ON a.profissional_id   = pr.id
                JOIN usuarios      pu ON pr.usuario_id       = pu.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY pt.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Totais financeiros por status.
     */
    public function totais(): array
    {
        return $this->conn->query(
            "SELECT
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END)  AS total_aprovado,
                SUM(CASE WHEN status = 'pending'  THEN amount ELSE 0 END)  AS total_pendente,
                SUM(CASE WHEN status = 'rejected' THEN amount ELSE 0 END)  AS total_recusado,
                SUM(CASE WHEN status = 'refunded' THEN amount ELSE 0 END)  AS total_reembolsado,
                COUNT(CASE WHEN status = 'approved' THEN 1 END)            AS qtd_aprovado,
                COUNT(CASE WHEN status = 'pending'  THEN 1 END)            AS qtd_pendente,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END)            AS qtd_recusado
             FROM payment_transactions"
        )->fetch(PDO::FETCH_ASSOC);
    }

    // ─── payment_logs ─────────────────────────────────────────────────

    public function log(string $transactionId, string $evento, array $dados = [], string $nivel = 'info'): void
    {
        try {
            $stmt = $this->conn->prepare(
                "INSERT INTO payment_logs (transaction_id, evento, dados, nivel, created_at)
                 VALUES (?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $transactionId,
                $evento,
                json_encode($dados, JSON_UNESCAPED_UNICODE),
                $nivel,
            ]);
        } catch (Throwable $e) {
            // Log não pode travar o fluxo principal
            error_log("[PaymentLog] Falha ao registrar log: " . $e->getMessage());
        }
    }

    public function findLogsByTransaction(string $transactionId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM payment_logs WHERE transaction_id = ? ORDER BY created_at ASC"
        );
        $stmt->execute([$transactionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── webhook_logs ─────────────────────────────────────────────────

    public function logWebhook(array $dados): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO webhook_logs
                (gateway, transaction_id, gateway_reference, payload, headers,
                 status, processado, erro, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $dados['gateway']            ?? '',
            $dados['transaction_id']     ?? null,
            $dados['gateway_reference']  ?? null,
            json_encode($dados['payload']  ?? [], JSON_UNESCAPED_UNICODE),
            json_encode($dados['headers']  ?? [], JSON_UNESCAPED_UNICODE),
            $dados['status']             ?? 'pending',
            (int) ($dados['processado']  ?? 0),
            $dados['erro']               ?? null,
        ]);
        return (int) $this->conn->lastInsertId();
    }

    public function marcarWebhookProcessado(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE webhook_logs SET processado = 1, erro = NULL WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function marcarWebhookErro(int $id, string $erro): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE webhook_logs SET processado = 0, erro = ? WHERE id = ?"
        );
        return $stmt->execute([$erro, $id]);
    }

    /**
     * Previne replay de webhook: verifica se gateway_reference já foi processado com sucesso.
     */
    public function webhookJaProcessado(string $gatewayReference): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM webhook_logs
             WHERE gateway_reference = ? AND processado = 1"
        );
        $stmt->execute([$gatewayReference]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
