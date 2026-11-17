<?php

/**
 * UsuarioService — regras de negócio de usuários e tokens de senha.
 */
class UsuarioService
{
    private UsuarioDAO $usuarioDAO;

    public function __construct()
    {
        $this->usuarioDAO = new UsuarioDAO();
    }

    // ─── Cadastro ─────────────────────────────────────────────────────────────

    public function register(array $dados): void
    {
        if (empty($dados['nome']) || empty($dados['email']) || empty($dados['senha'])) {
            throw new Exception("Preencha todos os campos.");
        }

        if (strlen($dados['nome']) < 3) {
            throw new Exception("Nome deve ter pelo menos 3 caracteres.");
        }

        if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("E-mail inválido.");
        }

        if (strlen($dados['senha']) < 6) {
            throw new Exception("Senha deve ter pelo menos 6 caracteres.");
        }

        if ($this->usuarioDAO->emailExists($dados['email'])) {
            throw new Exception("E-mail já cadastrado.");
        }

        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            $this->usuarioDAO->create($dados);
            $usuarioId = $this->usuarioDAO->getLastInsertId();

            // Cria registro na tabela clientes
            $stmt = $conn->prepare(
                "INSERT INTO clientes (usuario_id) VALUES (?)"
            );
            $stmt->execute([$usuarioId]);

            $conn->commit();
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            throw new Exception("Erro ao criar conta. Tente novamente.");
        }
    }

    // ─── Busca ─────────────────────────────────────────────────────────────────

    public function findByEmail(string $email): ?array
    {
        return $this->usuarioDAO->findByEmail($email);
    }

    public function findById(int $id): ?array
    {
        return $this->usuarioDAO->findById($id);
    }

    // ─── Tokens de redefinição de senha ───────────────────────────────────────

    public function invalidarTokensAnteriores(string $email): void
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "UPDATE password_resets SET usado = 1 WHERE email = ? AND usado = 0"
        );
        $stmt->execute([$email]);
    }

    public function salvarToken(string $email, string $token, string $expiraEm): void
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "INSERT INTO password_resets (email, token, expira_em) VALUES (?, ?, ?)"
        );
        $stmt->execute([$email, $token, $expiraEm]);
    }

    /**
     * Valida token — retorna dados do token ou null se inválido/expirado.
     */
    public function validarToken(string $token): ?array
    {
        $conn = Database::getConnection();
        $stmt = $conn->prepare(
            "SELECT id, email, expira_em
             FROM password_resets
             WHERE token = ? AND usado = 0 AND expira_em > NOW()
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function atualizarSenha(string $email, string $novaSenha): void
    {
        $hash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $this->usuarioDAO->updatePassword($email, $hash);
    }

    public function usarToken(int $tokenId): void
    {
        $conn = Database::getConnection();
        $conn->prepare("UPDATE password_resets SET usado = 1 WHERE id = ?")
             ->execute([$tokenId]);
    }
}
