<?php

/**
 * ServicoDAO
 * Compatível com banco antes e após a migration de detalhes.
 */
class ServicoDAO extends BaseDAO
{
    /** Cache estático: evita múltiplas queries SHOW COLUMNS por request */
    private static ?bool $temColunaDetalhe = null;

    /**
     * Verifica se as colunas de detalhe (slug, imagem, etc.) existem no banco.
     * Necessário para compatibilidade com banco antes da migration.
     */
    private function temColunaDetalhe(): bool
    {
        if (self::$temColunaDetalhe !== null) {
            return self::$temColunaDetalhe;
        }
        try {
            $result = $this->conn->query(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME   = 'servicos'
                   AND COLUMN_NAME  = 'slug'"
            )->fetchColumn();
            self::$temColunaDetalhe = (int)$result > 0;
        } catch (\Throwable $e) {
            self::$temColunaDetalhe = false;
        }
        return self::$temColunaDetalhe;
    }

    public function findAll(): array
    {
        if ($this->temColunaDetalhe()) {
            $sql = "SELECT s.id, s.nome, s.slug, s.descricao, s.imagem, s.valor,
                           s.duracao_minutos, s.status, c.nome AS categoria
                    FROM servicos s
                    LEFT JOIN categorias c ON s.categoria_id = c.id
                    WHERE s.status = 'ativo'
                    ORDER BY s.nome ASC";
        } else {
            $sql = "SELECT s.id, s.nome, s.descricao, s.valor,
                           s.duracao_minutos, s.status, c.nome AS categoria,
                           NULL AS slug, NULL AS imagem
                    FROM servicos s
                    LEFT JOIN categorias c ON s.categoria_id = c.id
                    WHERE s.status = 'ativo'
                    ORDER BY s.nome ASC";
        }
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAllAdmin(): array
    {
        if ($this->temColunaDetalhe()) {
            $sql = "SELECT s.id, s.nome, s.slug, s.descricao, s.valor,
                           s.duracao_minutos, s.status, c.nome AS categoria
                    FROM servicos s
                    LEFT JOIN categorias c ON s.categoria_id = c.id
                    ORDER BY s.nome ASC";
        } else {
            $sql = "SELECT s.id, s.nome, s.descricao, s.valor,
                           s.duracao_minutos, s.status, c.nome AS categoria,
                           NULL AS slug
                    FROM servicos s
                    LEFT JOIN categorias c ON s.categoria_id = c.id
                    ORDER BY s.nome ASC";
        }
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT s.*, c.nome AS categoria
             FROM servicos s
             LEFT JOIN categorias c ON s.categoria_id = c.id
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    /**
     * Busca serviço pelo slug — retorna null graciosamente se coluna não existir.
     */
    public function findBySlug(string $slug): ?array
    {
        if (!$this->temColunaDetalhe()) {
            return null;
        }
        $stmt = $this->conn->prepare(
            "SELECT s.*, c.nome AS categoria
             FROM servicos s
             LEFT JOIN categorias c ON s.categoria_id = c.id
             WHERE s.slug = ? AND s.status = 'ativo'
             LIMIT 1"
        );
        $stmt->execute([$slug]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        return $r ?: null;
    }

    public function countAll(): int
    {
        return (int) $this->conn->query(
            "SELECT COUNT(*) FROM servicos WHERE status = 'ativo'"
        )->fetchColumn();
    }

    /**
     * Lista todas as categorias disponíveis para o select do formulário.
     */
    public function findCategorias(): array
    {
        return $this->conn->query(
            "SELECT id, nome FROM categorias ORDER BY nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insere um novo serviço no banco.
     * Todos os valores já vêm validados e sanitizados pelo ServicoService.
     */
    public function insert(array $dados): int
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO servicos
                (categoria_id, nome, slug, descricao, valor, duracao_minutos, status)
             VALUES
                (:categoria_id, :nome, :slug, :descricao, :valor, :duracao_minutos, :status)"
        );

        $stmt->execute([
            ':categoria_id'    => $dados['categoria_id']    ?: null,
            ':nome'            => $dados['nome'],
            ':slug'            => $dados['slug'],
            ':descricao'       => $dados['descricao']       ?: null,
            ':valor'           => $dados['valor'],
            ':duracao_minutos' => $dados['duracao_minutos'],
            ':status'          => $dados['status'],
        ]);

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Verifica se já existe um serviço com o slug informado.
     */
    public function slugExiste(string $slug): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM servicos WHERE slug = ?"
        );
        $stmt->execute([$slug]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
