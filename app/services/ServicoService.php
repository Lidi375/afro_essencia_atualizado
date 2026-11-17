<?php

class ServicoService
{
    private ServicoDAO $servicoDAO;

    public function __construct()
    {
        $this->servicoDAO = new ServicoDAO();
    }

    // ── Existente: listar serviços ativos ────────────────────────
    public function listar(): array
    {
        return $this->servicoDAO->findAll();
    }

    // ── Novo: validar e cadastrar serviço ────────────────────────
    /**
     * Valida os dados do formulário e persiste o novo serviço.
     * Toda regra de negócio permanece aqui, fora do Controller e da View.
     *
     * @throws Exception com mensagem amigável em caso de erro de validação
     */
    public function criar(array $post): int
    {
        // 1. Sanitização básica
        $nome            = trim($post['nome']            ?? '');
        $descricao       = trim($post['descricao']       ?? '');
        $valorRaw        = str_replace(',', '.', trim($post['valor'] ?? ''));
        $duracaoRaw      = trim($post['duracao_minutos'] ?? '');
        $categoriaIdRaw  = trim($post['categoria_id']    ?? '');
        $status          = trim($post['status']          ?? 'ativo');

        // 2. Validações obrigatórias
        if ($nome === '') {
            throw new Exception('O nome do serviço é obrigatório.');
        }
        if (mb_strlen($nome) > 120) {
            throw new Exception('O nome não pode ter mais de 120 caracteres.');
        }
        if ($valorRaw === '' || !is_numeric($valorRaw) || (float)$valorRaw < 0) {
            throw new Exception('Informe um valor válido para o serviço.');
        }
        if ($duracaoRaw === '' || !ctype_digit($duracaoRaw) || (int)$duracaoRaw < 1) {
            throw new Exception('Informe uma duração válida em minutos.');
        }
        if (!in_array($status, ['ativo', 'inativo'], true)) {
            throw new Exception('Status inválido.');
        }

        // 3. Slug gerado automaticamente a partir do nome
        $slug = $this->gerarSlug($nome);

        // 4. Garante unicidade do slug
        $slugBase = $slug;
        $contador = 1;
        while ($this->servicoDAO->slugExiste($slug)) {
            $slug = $slugBase . '-' . $contador;
            $contador++;
        }

        // 5. Monta array limpo para persistência
        $dados = [
            'categoria_id'    => $categoriaIdRaw !== '' ? (int)$categoriaIdRaw : null,
            'nome'            => $nome,
            'slug'            => $slug,
            'descricao'       => $descricao !== '' ? $descricao : null,
            'valor'           => (float)$valorRaw,
            'duracao_minutos' => (int)$duracaoRaw,
            'status'          => $status,
        ];

        return $this->servicoDAO->insert($dados);
    }

    /**
     * Gera slug URL-friendly a partir de uma string em português.
     */
    private function gerarSlug(string $texto): string
    {
        $slug = mb_strtolower($texto, 'UTF-8');

        // Transliterar caracteres acentuados
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);

        // Manter apenas letras, números e espaços
        $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);

        // Substituir espaços por hífens
        $slug = preg_replace('/[\s]+/', '-', trim($slug));

        // Remover hífens múltiplos
        $slug = preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }
}
