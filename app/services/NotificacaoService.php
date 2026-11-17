<?php

/**
 * NotificacaoService — cria e lista notificações de usuários.
 */
class NotificacaoService
{
    private function dao(): NotificacaoDAO
    {
        return new NotificacaoDAO();
    }

    public function listar(int $usuarioId): array
    {
        return $this->dao()->findByUsuario($usuarioId);
    }

    public function marcarTodasLidas(int $usuarioId): void
    {
        $this->dao()->marcarTodasLidas($usuarioId);
    }

    public function notificarAgendamentoCriado(int $usuarioId, string $servico, string $data): void
    {
        $this->dao()->create(
            $usuarioId,
            'Agendamento confirmado',
            "Seu agendamento de \"{$servico}\" em " . date('d/m/Y', strtotime($data)) . " foi registrado com sucesso."
        );
    }

    public function notificarAgendamentoCancelado(int $usuarioId, string $servico, string $data): void
    {
        $this->dao()->create(
            $usuarioId,
            'Agendamento cancelado',
            "O agendamento de \"{$servico}\" em " . date('d/m/Y', strtotime($data)) . " foi cancelado."
        );
    }

    public function notificarAgendamentoReagendado(int $usuarioId, string $servico, string $novaData, string $novaHora): void
    {
        $this->dao()->create(
            $usuarioId,
            'Agendamento reagendado',
            "Seu agendamento de \"{$servico}\" foi remarcado para " . date('d/m/Y', strtotime($novaData)) . " às {$novaHora}."
        );
    }
    // ─── Novos métodos de pagamento (adicionado) ──────────────────────────────

    public function notificarPagamentoConfirmado(int $usuarioId, string $servico, float $valor, string $metodo): void
    {
        $valorFormatado  = 'R$ ' . number_format($valor, 2, ',', '.');
        $metodoFormatado = ucfirst($metodo);
        $this->dao()->create(
            $usuarioId,
            'Pagamento confirmado',
            "Seu pagamento de {$valorFormatado} via {$metodoFormatado} pelo serviço \"{$servico}\" foi confirmado com sucesso."
        );
    }

    public function notificarPagamentoEstornado(int $usuarioId, string $servico, float $valor): void
    {
        $valorFormatado = 'R$ ' . number_format($valor, 2, ',', '.');
        $this->dao()->create(
            $usuarioId,
            'Pagamento estornado',
            "O pagamento de {$valorFormatado} pelo serviço \"{$servico}\" foi estornado. Seu agendamento voltou ao status pendente."
        );
    }

}
