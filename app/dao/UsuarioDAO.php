<?php

/**
 * UsuarioDAO — acesso a dados de usuários.
 */
class UsuarioDAO extends BaseDAO
{
    public function findAll(): array
    {
        return $this->conn->query(
            "SELECT id, nome, email, tipo_usuario, status, created_at
             FROM usuarios ORDER BY nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, email, tipo_usuario, telefone, foto, status, created_at
             FROM usuarios WHERE id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, nome, email, senha, tipo_usuario, telefone, foto, status
             FROM usuarios WHERE email = ? LIMIT 1"
        );
        $stmt->execute([$email]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM usuarios WHERE email = ?"
        );
        $stmt->execute([$email]);
        return $stmt->fetchColumn() > 0;
    }

    public function create(array $dados): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO usuarios (nome, email, senha, tipo_usuario, status)
             VALUES (?, ?, ?, 'cliente', 'ativo')"
        );
        return $stmt->execute([
            $dados['nome'],
            $dados['email'],
            password_hash($dados['senha'], PASSWORD_BCRYPT),
        ]);
    }

    public function getLastInsertId(): int
    {
        return (int) $this->conn->lastInsertId();
    }

    public function updatePassword(string $email, string $hashSenha): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET senha = ? WHERE email = ?"
        );
        return $stmt->execute([$hashSenha, $email]);
    }

    public function updateTipo(int $id, string $tipo): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET tipo_usuario = ? WHERE id = ?"
        );
        return $stmt->execute([$tipo, $id]);
    }

    public function desativar(int $id): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE usuarios SET status = 'inativo' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function countAll(): int
    {
        return (int) $this->conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    }
}
