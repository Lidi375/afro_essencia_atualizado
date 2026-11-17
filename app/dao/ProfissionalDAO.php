<?php

class ProfissionalDAO extends BaseDAO
{
    /**
     * Cria registro de profissional
     */
    public function create(array $dados): bool
    {
        $sql  = "INSERT INTO profissionais (usuario_id, biografia, experiencia_anos, status) VALUES (?, ?, ?, 'ativo')";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$dados['usuario_id'], $dados['biografia'], $dados['experiencia']]);
    }

    /**
     * Lista profissionais ativos com nome do usuário
     */
    public function findAll(): array
    {
        $sql = "SELECT
                    p.id AS profissional_id,
                    p.id,
                    p.usuario_id,
                    u.nome,
                    u.email,
                    p.biografia,
                    p.experiencia_anos,
                    p.status
                FROM profissionais p
                JOIN usuarios u ON p.usuario_id = u.id
                WHERE p.status = 'ativo'
                ORDER BY u.nome ASC";

        return $this->conn->query($sql)->fetchAll();
    }

    /**
     * Lista todos os profissionais (inclusive inativos) para admin
     */
    public function findAllAdmin(): array
    {
        $sql = "SELECT
                    p.id AS profissional_id,
                    p.id,
                    p.usuario_id,
                    u.nome,
                    u.email,
                    p.biografia,
                    p.experiencia_anos,
                    p.status
                FROM profissionais p
                JOIN usuarios u ON p.usuario_id = u.id
                ORDER BY u.nome ASC";

        return $this->conn->query($sql)->fetchAll();
    }

    /**
     * Conta profissionais ativos
     */
    public function countAll(): int
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM profissionais WHERE status = 'ativo'")->fetchColumn();
    }

    /**
     * Busca profissional por ID (coluna id da tabela profissionais)
     */
    public function findById($id): ?array
    {
        $sql  = "SELECT p.*, u.nome, u.email FROM profissionais p JOIN usuarios u ON p.usuario_id = u.id WHERE p.id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca profissional pelo usuario_id
     */
    public function findByUsuarioId($usuarioId): ?array
    {
        $sql  = "SELECT p.*, u.nome, u.email FROM profissionais p JOIN usuarios u ON p.usuario_id = u.id WHERE p.usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuarioId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Desativa profissional (usa inativação, não DELETE)
     */
    public function desativar($id): bool
    {
        $stmt = $this->conn->prepare("UPDATE profissionais SET status = 'inativo' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Reativa profissional
     */
    public function reativar($id): bool
    {
        $stmt = $this->conn->prepare("UPDATE profissionais SET status = 'ativo' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
