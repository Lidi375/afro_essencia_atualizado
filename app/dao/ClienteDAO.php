<?php

/**
 * ClienteDAO — acesso a dados de clientes.
 */
class ClienteDAO extends BaseDAO
{
    public function findAll(): array
    {
        return $this->conn->query(
            "SELECT c.id, c.usuario_id, u.nome, u.email, u.status,
                    c.data_nascimento, c.instagram, c.created_at
             FROM clientes c
             JOIN usuarios u ON c.usuario_id = u.id
             ORDER BY u.nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nome, u.email, u.telefone, u.status
             FROM clientes c
             JOIN usuarios u ON c.usuario_id = u.id
             WHERE c.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function findByUsuarioId(int $usuarioId): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT c.*, u.nome, u.email, u.telefone
             FROM clientes c
             JOIN usuarios u ON c.usuario_id = u.id
             WHERE c.usuario_id = ? LIMIT 1"
        );
        $stmt->execute([$usuarioId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function countAll(): int
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM clientes")->fetchColumn();
    }

    public function countNovosHoje(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM clientes WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();
    }

    public function countNovosNoMes(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM clientes
             WHERE MONTH(created_at) = MONTH(CURDATE())
               AND YEAR(created_at)  = YEAR(CURDATE())"
        )->fetchColumn();
    }
}
