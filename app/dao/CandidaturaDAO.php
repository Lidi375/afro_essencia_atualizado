<?php

/**
 * CandidaturaDAO — Acesso a dados de candidaturas Trabalhe Conosco.
 */
class CandidaturaDAO extends BaseDAO
{
    // ─── Criação ──────────────────────────────────────────────────────────────

    public function create(array $dados): int
    {
        $sql = "INSERT INTO candidaturas_profissionais
                    (nome, email, telefone, cidade, estado, especialidade,
                     experiencia_anos, descricao, instagram, portfolio_url,
                     arquivo_curriculo, arquivo_certificado, status)
                VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            $dados['nome'],
            $dados['email'],
            $dados['telefone'],
            $dados['cidade'],
            $dados['estado'],
            $dados['especialidade'],
            (int) $dados['experiencia_anos'],
            $dados['descricao'],
            $dados['instagram']        ?? '',
            $dados['portfolio_url']    ?? '',
            $dados['arquivo_curriculo']   ?? '',
            $dados['arquivo_certificado'] ?? '',
        ]);

        return (int) $this->conn->lastInsertId();
    }

    // ─── Leitura ──────────────────────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM candidaturas_profissionais WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM candidaturas_profissionais WHERE email = ? ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Lista candidaturas com filtros opcionais de status e busca textual.
     */
    public function findAllFiltered(string $status = '', string $busca = ''): array
    {
        $where  = [];
        $params = [];

        if ($status !== '') {
            $where[]  = 'status = ?';
            $params[] = $status;
        }

        if ($busca !== '') {
            $where[]  = '(nome LIKE ? OR email LIKE ? OR especialidade LIKE ? OR cidade LIKE ?)';
            $like     = '%' . $busca . '%';
            $params   = array_merge($params, [$like, $like, $like, $like]);
        }

        $sql = "SELECT * FROM candidaturas_profissionais";
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Contadores ───────────────────────────────────────────────────────────

    public function countByStatus(string $status): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM candidaturas_profissionais WHERE status = ?"
        );
        $stmt->execute([$status]);
        return (int) $stmt->fetchColumn();
    }

    public function countGroupByStatus(): array
    {
        $stmt = $this->conn->query(
            "SELECT status, COUNT(*) AS total
             FROM candidaturas_profissionais
             GROUP BY status"
        );
        $result = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $result[$row['status']] = (int) $row['total'];
        }
        return $result;
    }

    // ─── Atualização ──────────────────────────────────────────────────────────

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE candidaturas_profissionais SET status = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$status, $id]);
    }

    // ─── Histórico ────────────────────────────────────────────────────────────

    public function insertHistorico(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO candidaturas_historico
                 (candidatura_id, status_anterior, status_novo, observacao, admin_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $dados['candidatura_id'],
            $dados['status_anterior'],
            $dados['status_novo'],
            $dados['observacao'] ?? '',
            $dados['admin_id']   ?? null,
        ]);
    }

    public function findHistoricoByCandidaturaId(int $candidaturaId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT h.*, u.nome AS admin_nome
             FROM candidaturas_historico h
             LEFT JOIN usuarios u ON h.admin_id = u.id
             WHERE h.candidatura_id = ?
             ORDER BY h.created_at DESC"
        );
        $stmt->execute([$candidaturaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Verificação de candidatura recente (anti-spam) ───────────────────────

    public function existeCandidaturaRecentePorEmail(string $email, int $diasAtras = 30): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM candidaturas_profissionais
             WHERE email = ?
               AND status NOT IN ('reprovado')
               AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)"
        );
        $stmt->execute([$email, $diasAtras]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
