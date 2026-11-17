<?php

/**
 * CandidaturaService
 * Regras de negócio do módulo Trabalhe Conosco.
 */
class CandidaturaService
{
    private CandidaturaDAO $dao;

    public function __construct()
    {
        $this->dao = new CandidaturaDAO();
    }

    // ─── Submissão pública ────────────────────────────────────────────────────

    /**
     * Valida, salva e notifica o admin sobre nova candidatura.
     *
     * @param array $post    Dados do formulário ($_POST)
     * @param array $files   Arquivos enviados ($_FILES)
     * @return int ID da candidatura criada
     * @throws Exception com mensagem amigável
     */
    public function submeter(array $post, array $files): int
    {
        $dados = $this->validarEsanitizar($post);

        // Verificar candidatura recente (anti-spam: 30 dias)
        if ($this->dao->existeCandidaturaRecentePorEmail($dados['email'])) {
            throw new Exception(
                "Já existe uma candidatura recente com este e-mail. " .
                "Aguarde 30 dias para enviar nova candidatura."
            );
        }

        // Processa uploads
        $dados['arquivo_curriculo']    = $this->processarUpload($files, 'curriculo', true);
        $dados['arquivo_certificado']  = $this->processarUpload($files, 'certificado', false);

        $id = $this->dao->create($dados);

        // Notifica administradores por e-mail (silencioso se falhar)
        try {
            $emailService = new EmailService();
            $emailService->notificarNovaCandidatura($dados['nome'], $dados['email'], $dados['especialidade']);
        } catch (Throwable $e) {
            error_log('CandidaturaService::submeter — e-mail admin falhou: ' . $e->getMessage());
        }

        return $id;
    }

    // ─── Gestão administrativa ────────────────────────────────────────────────

    /**
     * Atualiza status e registra histórico.
     */
    public function atualizarStatus(int $id, string $novoStatus, string $observacao, int $adminId): void
    {
        $candidatura = $this->dao->findById($id);

        if (!$candidatura) {
            throw new Exception("Candidatura não encontrada.");
        }

        if (!in_array($novoStatus, Candidatura::statusValidos(), true)) {
            throw new Exception("Status inválido.");
        }

        $this->dao->updateStatus($id, $novoStatus);

        $this->dao->insertHistorico([
            'candidatura_id'  => $id,
            'status_anterior' => $candidatura['status'],
            'status_novo'     => $novoStatus,
            'observacao'      => $observacao,
            'admin_id'        => $adminId,
        ]);
    }

    /**
     * Aprova candidatura: cria usuário + profissional de forma atômica.
     * - Se já existir usuário com mesmo e-mail → promove para profissional.
     * - Se não existir → cria novo usuário e profissional.
     * Envia e-mail de boas-vindas ao final.
     */
    public function aprovarCandidatura(int $candidaturaId): array
    {
        $candidatura = $this->dao->findById($candidaturaId);

        if (!$candidatura) {
            throw new Exception("Candidatura não encontrada.");
        }

        $conn = Database::getConnection();

        try {
            $conn->beginTransaction();

            $usuarioDAO      = new UsuarioDAO();
            $profissionalDAO = new ProfissionalDAO();
            $usuarioExistente = $usuarioDAO->findByEmail($candidatura['email']);

            if ($usuarioExistente) {
                // ── Caminho A: usuário já existe → promover ──────────────────
                $usuarioId = (int) $usuarioExistente['id'];

                // Garante que o tipo seja profissional
                $usuarioDAO->updateTipo($usuarioId, 'profissional');

                // Verifica se já tem registro de profissional
                $profExistente = $profissionalDAO->findByUsuarioId($usuarioId);

                if ($profExistente) {
                    // Reativa se estava inativo
                    $profissionalDAO->reativar((int) $profExistente['id']);
                } else {
                    // Cria novo registro de profissional
                    $profissionalDAO->create([
                        'usuario_id'  => $usuarioId,
                        'biografia'   => $candidatura['descricao'],
                        'experiencia' => (int) $candidatura['experiencia_anos'],
                    ]);
                }

            } else {
                // ── Caminho B: novo usuário ──────────────────────────────────
                $senhaTemporaria = $this->gerarSenhaTemporaria();

                $stmt = $conn->prepare(
                    "INSERT INTO usuarios (nome, email, senha, tipo_usuario, telefone, status)
                     VALUES (?, ?, ?, 'profissional', ?, 'ativo')"
                );
                $stmt->execute([
                    $candidatura['nome'],
                    $candidatura['email'],
                    password_hash($senhaTemporaria, PASSWORD_BCRYPT),
                    $candidatura['telefone'],
                ]);
                $usuarioId = (int) $conn->lastInsertId();

                $profissionalDAO->create([
                    'usuario_id'  => $usuarioId,
                    'biografia'   => $candidatura['descricao'],
                    'experiencia' => (int) $candidatura['experiencia_anos'],
                ]);
            }

            $conn->commit();

            // Envia e-mail de boas-vindas com link de criação de senha
            $this->enviarEmailBoasVindas(
                $candidatura['email'],
                $candidatura['nome'],
                $usuarioId
            );

            return [
                'usuario_id' => $usuarioId,
                'nome'       => $candidatura['nome'],
                'email'      => $candidatura['email'],
            ];

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('CandidaturaService::aprovarCandidatura — ' . $e->getMessage());
            throw new Exception("Erro ao criar perfil profissional: " . $e->getMessage());
        }
    }

    // ─── Validação e sanitização ──────────────────────────────────────────────

    private function validarEsanitizar(array $post): array
    {
        $erros = [];

        $nome          = trim(strip_tags($post['nome']          ?? ''));
        $email         = trim($post['email']                    ?? '');
        $telefone      = trim(preg_replace('/[^\d\s\-\(\)\+]/', '', $post['telefone'] ?? ''));
        $cidade        = trim(strip_tags($post['cidade']        ?? ''));
        $estado        = trim(strip_tags($post['estado']        ?? ''));
        $especialidade = trim(strip_tags($post['especialidade'] ?? ''));
        $experiencia   = (int) ($post['experiencia_anos']       ?? 0);
        $descricao     = trim(strip_tags($post['descricao']     ?? ''));
        $instagram     = trim(strip_tags($post['instagram']     ?? ''));
        $portfolio     = trim($post['portfolio_url']             ?? '');

        if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120) {
            $erros[] = "Nome deve ter entre 3 e 120 caracteres.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido.";
        }

        if (mb_strlen($telefone) < 8) {
            $erros[] = "Telefone inválido.";
        }

        if (mb_strlen($cidade) < 2 || mb_strlen($cidade) > 80) {
            $erros[] = "Cidade inválida.";
        }

        if (mb_strlen($estado) !== 2) {
            $erros[] = "Estado deve ser a sigla com 2 letras (ex: MG).";
        }

        if (mb_strlen($especialidade) < 3 || mb_strlen($especialidade) > 120) {
            $erros[] = "Especialidade inválida.";
        }

        if ($experiencia < 0 || $experiencia > 60) {
            $erros[] = "Anos de experiência devem estar entre 0 e 60.";
        }

        if (mb_strlen($descricao) < 30 || mb_strlen($descricao) > 2000) {
            $erros[] = "Descrição profissional deve ter entre 30 e 2000 caracteres.";
        }

        if ($portfolio !== '' && !filter_var($portfolio, FILTER_VALIDATE_URL)) {
            $erros[] = "URL do portfólio é inválida.";
        }

        if ($erros) {
            throw new Exception(implode('<br>', $erros));
        }

        return compact(
            'nome', 'email', 'telefone', 'cidade', 'estado',
            'especialidade', 'experiencia', 'descricao', 'instagram', 'portfolio'
        ) + ['portfolio_url' => $portfolio, 'experiencia_anos' => $experiencia];
    }

    // ─── Upload seguro ────────────────────────────────────────────────────────

    private function processarUpload(array $files, string $campo, bool $obrigatorio): string
    {
        $uploadDir = Candidatura::UPLOAD_DIR;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }

        // Protege diretório com .htaccess se não existir
        $htaccess = $uploadDir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Options -Indexes\nDeny from all\n");
        }

        if (
            !isset($files[$campo]) ||
            $files[$campo]['error'] === UPLOAD_ERR_NO_FILE ||
            $files[$campo]['size'] === 0
        ) {
            if ($obrigatorio) {
                throw new Exception("O arquivo de currículo é obrigatório.");
            }
            return '';
        }

        $arquivo = $files[$campo];

        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erro no upload do arquivo. Código: " . $arquivo['error']);
        }

        // Limite de tamanho: 5 MB
        if ($arquivo['size'] > Candidatura::MAX_FILE_SIZE_BYTES) {
            throw new Exception("Arquivo muito grande. Máximo permitido: 5 MB.");
        }

        // Validação MIME real (não apenas extensão)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $arquivo['tmp_name']);
        finfo_close($finfo);

        if ($mimeReal !== 'application/pdf') {
            throw new Exception("Apenas arquivos PDF são aceitos ({$campo}).");
        }

        // Valida conteúdo mínimo de PDF (magic bytes)
        $handle = fopen($arquivo['tmp_name'], 'rb');
        $magic  = fread($handle, 4);
        fclose($handle);

        if ($magic !== '%PDF') {
            throw new Exception("O arquivo enviado não é um PDF válido.");
        }

        // Gera nome seguro e único
        $nomeSeguro = sprintf(
            '%s_%s_%s.pdf',
            $campo,
            date('Ymd_His'),
            bin2hex(random_bytes(8))
        );

        $destino = $uploadDir . $nomeSeguro;

        if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
            throw new Exception("Falha ao salvar o arquivo. Tente novamente.");
        }

        return $destino;
    }

    // ─── E-mail de boas-vindas ────────────────────────────────────────────────

    private function enviarEmailBoasVindas(string $email, string $nome, int $usuarioId): void
    {
        try {
            $usuarioService = new UsuarioService();
            $usuarioService->invalidarTokensAnteriores($email);

            $token    = bin2hex(random_bytes(32));
            $expiraEm = date('Y-m-d H:i:s', strtotime('+48 hours'));
            $usuarioService->salvarToken($email, $token, $expiraEm);

            $emailService = new EmailService();
            $emailService->enviarBoasVindasProfissional($email, $nome, $token);

        } catch (Throwable $e) {
            // E-mail de boas-vindas não é crítico — só loga
            error_log('CandidaturaService::enviarEmailBoasVindas — ' . $e->getMessage());
        }
    }

    // ─── Utilitários ──────────────────────────────────────────────────────────

    private function gerarSenhaTemporaria(int $tamanho = 12): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
        $senha = '';
        for ($i = 0; $i < $tamanho; $i++) {
            $senha .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $senha;
    }
}
