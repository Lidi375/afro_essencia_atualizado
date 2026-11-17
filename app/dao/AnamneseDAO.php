<?php

/**
 * AnamneseDAO
 * Operações de banco de dados para a ficha de anamnese.
 */
class AnamneseDAO extends BaseDAO
{
    // ==============================================
    // CRIAR anamnese
    // ==============================================
    public function create(array $dados): bool
    {
        $sql = "INSERT INTO anamneses
                (cliente_id, tipo_cabelo, subtipo_cabelo, nivel_hidratacao, comprimento,
                 densidade, usa_quimica, tipo_quimica, ultima_quimica, frequencia_lavagem,
                 usa_calor, frequencia_calor, problemas_couro, alergias,
                 objetivo, servicos_interesse, observacoes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $dados['cliente_id'],
            $dados['tipo_cabelo']        ?? null,
            $dados['subtipo_cabelo']     ?? null,
            $dados['nivel_hidratacao']   ?? null,
            $dados['comprimento']        ?? null,
            $dados['densidade']          ?? null,
            $dados['usa_quimica']        ?? 0,
            $dados['tipo_quimica']       ?? null,
            $dados['ultima_quimica']     ?? null,
            $dados['frequencia_lavagem'] ?? null,
            $dados['usa_calor']          ?? 0,
            $dados['frequencia_calor']   ?? null,
            $dados['problemas_couro']    ?? null,
            $dados['alergias']           ?? null,
            $dados['objetivo']           ?? null,
            $dados['servicos_interesse'] ?? null,
            $dados['observacoes']        ?? null,
        ]);
    }

    // ==============================================
    // ATUALIZAR anamnese existente
    // ==============================================
    public function update(int $id, array $dados): bool
    {
        $sql = "UPDATE anamneses SET
                    tipo_cabelo        = ?,
                    subtipo_cabelo     = ?,
                    nivel_hidratacao   = ?,
                    comprimento        = ?,
                    densidade          = ?,
                    usa_quimica        = ?,
                    tipo_quimica       = ?,
                    ultima_quimica     = ?,
                    frequencia_lavagem = ?,
                    usa_calor          = ?,
                    frequencia_calor   = ?,
                    problemas_couro    = ?,
                    alergias           = ?,
                    objetivo           = ?,
                    servicos_interesse = ?,
                    observacoes        = ?
                WHERE id = ?";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            $dados['tipo_cabelo']        ?? null,
            $dados['subtipo_cabelo']     ?? null,
            $dados['nivel_hidratacao']   ?? null,
            $dados['comprimento']        ?? null,
            $dados['densidade']          ?? null,
            $dados['usa_quimica']        ?? 0,
            $dados['tipo_quimica']       ?? null,
            $dados['ultima_quimica']     ?? null,
            $dados['frequencia_lavagem'] ?? null,
            $dados['usa_calor']          ?? 0,
            $dados['frequencia_calor']   ?? null,
            $dados['problemas_couro']    ?? null,
            $dados['alergias']           ?? null,
            $dados['objetivo']           ?? null,
            $dados['servicos_interesse'] ?? null,
            $dados['observacoes']        ?? null,
            $id,
        ]);
    }

    // ==============================================
    // BUSCAR por cliente_id (uma por cliente)
    // ==============================================
    public function findByClienteId(int $clienteId): ?array
    {
        $sql  = "SELECT * FROM anamneses WHERE cliente_id = ? ORDER BY updated_at DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$clienteId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ==============================================
    // BUSCAR por id
    // ==============================================
    public function findById(int $id): ?array
    {
        $sql  = "SELECT * FROM anamneses WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ==============================================
    // LISTAR TODAS (admin) com nome do cliente
    // ==============================================
    public function findAllAdmin(): array
    {
        $sql = "SELECT
                    a.id,
                    a.cliente_id,
                    a.tipo_cabelo,
                    a.subtipo_cabelo,
                    a.nivel_hidratacao,
                    a.comprimento,
                    a.objetivo,
                    a.updated_at,
                    u.nome   AS cliente_nome,
                    u.email  AS cliente_email
                FROM anamneses a
                JOIN clientes  c ON a.cliente_id = c.id
                JOIN usuarios  u ON c.usuario_id = u.id
                ORDER BY a.updated_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==============================================
    // BUSCAR DETALHE COMPLETO (admin) por anamnese id
    // ==============================================
    public function findDetalheAdmin(int $id): ?array
    {
        $sql = "SELECT
                    a.*,
                    u.nome   AS cliente_nome,
                    u.email  AS cliente_email
                FROM anamneses a
                JOIN clientes  c ON a.cliente_id = c.id
                JOIN usuarios  u ON c.usuario_id = u.id
                WHERE a.id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ==============================================
    // CONTAR total de fichas (admin)
    // ==============================================
    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM anamneses");
        return (int) $stmt->fetchColumn();
    }
}
