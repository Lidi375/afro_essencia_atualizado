<?php

/**
 * Candidatura — Model de candidatura Trabalhe Conosco.
 */
class Candidatura
{
    public const STATUS_PENDENTE    = 'pendente';
    public const STATUS_EM_ANALISE  = 'em_analise';
    public const STATUS_APROVADO    = 'aprovado';
    public const STATUS_REPROVADO   = 'reprovado';

    public const STATUS_LABELS = [
        self::STATUS_PENDENTE   => 'Pendente',
        self::STATUS_EM_ANALISE => 'Em Análise',
        self::STATUS_APROVADO   => 'Aprovado',
        self::STATUS_REPROVADO  => 'Reprovado',
    ];

    public const UPLOAD_DIR = __DIR__ . '/../../uploads/candidaturas/';

    public const MAX_FILE_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB

    // ─── Campos ───────────────────────────────────────────────────────────────
    public int    $id                 = 0;
    public string $nome               = '';
    public string $email              = '';
    public string $telefone           = '';
    public string $cidade             = '';
    public string $estado             = '';
    public string $especialidade      = '';
    public int    $experiencia_anos   = 0;
    public string $descricao          = '';
    public string $instagram          = '';
    public string $portfolio_url      = '';
    public string $arquivo_curriculo  = '';
    public string $arquivo_certificado = '';
    public string $status             = self::STATUS_PENDENTE;
    public string $created_at         = '';
    public string $updated_at         = '';

    public static function statusLabel(string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ucfirst($status);
    }

    public static function statusValidos(): array
    {
        return array_keys(self::STATUS_LABELS);
    }
}
