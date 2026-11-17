<?php

/**
 * NotificacaoDAO
 */
class NotificacaoDAO extends BaseDAO
{
    public function create(int $usuarioId, string $titulo, string $mensagem): bool
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO notificacoes (usuario_id, titulo, mensagem) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$usuarioId, $titulo, $mensagem]);
    }

    public function findByUsuario(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT id, titulo, mensagem, lida, created_at
             FROM notificacoes
             WHERE usuario_id = ?
             ORDER BY created_at DESC
             LIMIT 50"
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countNaoLidas(int $usuarioId): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM notificacoes WHERE usuario_id = ? AND lida = 0"
        );
        $stmt->execute([$usuarioId]);
        return (int) $stmt->fetchColumn();
    }

    public function marcarTodasLidas(int $usuarioId): bool
    {
        $stmt = $this->conn->prepare(
            "UPDATE notificacoes SET lida = 1 WHERE usuario_id = ? AND lida = 0"
        );
        return $stmt->execute([$usuarioId]);
    }
}
