<?php

/**
 * AvaliacaoService — regras de negócio das avaliações.
 */
class AvaliacaoService
{
    private AvaliacaoDAO $avaliacaoDAO;

    public function __construct()
    {
        $this->avaliacaoDAO = new AvaliacaoDAO();
    }

    public function criar(array $dados): void
    {
        $nota = (int) ($dados['nota'] ?? 0);
        if ($nota < 1 || $nota > 5) {
            throw new Exception("A nota deve ser entre 1 e 5.");
        }

        $comentario = trim($dados['comentario'] ?? '');
        if (strlen($comentario) > 1000) {
            throw new Exception("O comentário deve ter no máximo 1000 caracteres.");
        }

        if ($this->avaliacaoDAO->jaAvaliou((int) $dados['agendamento_id'])) {
            throw new Exception("Este agendamento já foi avaliado.");
        }

        $this->avaliacaoDAO->create([
            'agendamento_id'  => $dados['agendamento_id'],
            'profissional_id' => $dados['profissional_id'],
            'cliente_id'      => $dados['cliente_id'],
            'nota'            => $nota,
            'comentario'      => $comentario ?: null,
        ]);
    }

    public function listarByCliente(int $clienteId): array
    {
        return $this->avaliacaoDAO->findByCliente($clienteId);
    }

    public function listarAll(): array
    {
        return $this->avaliacaoDAO->findAll();
    }
}
