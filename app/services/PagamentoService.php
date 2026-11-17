<?php

/**
 * PagamentoService — lógica de negócio de pagamentos (fluxo real com gateway).
 *
 * REGRA FUNDAMENTAL: nenhum agendamento é marcado como pago sem confirmação
 * real do gateway via webhook. A simples escolha de método de pagamento
 * NUNCA confirma um pagamento.
 */
class PagamentoService
{
    private PagamentoDAO           $pagamentoDAO;
    private PaymentTransactionDAO  $transactionDAO;

    public function __construct()
    {
        $this->pagamentoDAO   = new PagamentoDAO();
        $this->transactionDAO = new PaymentTransactionDAO();
    }

    // ═══════════════════════════════════════════════════════════════════
    // FLUXO GATEWAY — cliente
    // ═══════════════════════════════════════════════════════════════════

    public function iniciarCheckout(int $agendamentoId, int $usuarioId): array
    {
        $agendamentoDAO = new AgendamentoDAO();
        $agendamento    = $agendamentoDAO->findById($agendamentoId);

        if (!$agendamento) {
            throw new Exception("Agendamento não encontrado.");
        }

        $clienteDAO = new ClienteDAO();
        $cliente    = $clienteDAO->findByUsuarioId($usuarioId);

        if (!$cliente || (int) $agendamento['cliente_id'] !== (int) $cliente['id']) {
            throw new Exception("Acesso negado: este agendamento não pertence a você.");
        }

        if ($this->transactionDAO->hasApproved($agendamentoId)) {
            throw new Exception("Este agendamento já foi pago.");
        }

        $statusPagaveis = ['pendente', 'confirmado', 'aguardando_pagamento'];
        if (!in_array($agendamento['status'], $statusPagaveis, true)) {
            throw new Exception("Este agendamento não pode ser pago (status: {$agendamento['status']}).");
        }

        $servicoDAO = new ServicoDAO();
        $servico    = $servicoDAO->findById((int) $agendamento['servico_id']);

        if (!$servico || (float) $servico['valor'] <= 0) {
            throw new Exception("Não foi possível determinar o valor do serviço.");
        }

        $valor = (float) $servico['valor'];

        $usuarioDAO = new UsuarioDAO();
        $usuario    = $usuarioDAO->findById($usuarioId);

        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            $this->transactionDAO->cancelPending($agendamentoId);

            $gateway = GatewayFactory::make();

            $transactionId = $this->transactionDAO->createTransaction([
                'agendamento_id' => $agendamentoId,
                'gateway'        => $gateway->getName(),
                'amount'         => $valor,
            ]);

            $base = defined('APP_BASE_URL') ? rtrim(APP_BASE_URL, '/') : '';
            $checkoutData = $gateway->createCheckout([
                'agendamento_id' => $agendamentoId,
                'transaction_id' => $transactionId,
                'valor'          => $valor,
                'descricao'      => "Afro Essência - " . $servico['nome'],
                'cliente_nome'   => $usuario['nome']  ?? 'Cliente',
                'cliente_email'  => $usuario['email'] ?? '',
                'url_sucesso'    => "{$base}/pagamento/retorno?status=success&tid={$transactionId}",
                'url_pendente'   => "{$base}/pagamento/retorno?status=pending&tid={$transactionId}",
                'url_falha'      => "{$base}/pagamento/retorno?status=failure&tid={$transactionId}",
                'url_webhook'    => "{$base}/pagamento/webhook/{$gateway->getName()}",
            ]);

            $this->transactionDAO->setGatewayReference($transactionId, $checkoutData['gateway_reference']);

            $conn->prepare("UPDATE agendamentos SET status = 'aguardando_pagamento' WHERE id = ?")
                 ->execute([$agendamentoId]);

            $this->transactionDAO->log($transactionId, 'checkout_criado', [
                'gateway'           => $gateway->getName(),
                'gateway_reference' => $checkoutData['gateway_reference'],
                'valor'             => $valor,
            ]);

            $conn->commit();

            return [
                'checkout_url'   => $checkoutData['checkout_url'],
                'transaction_id' => $transactionId,
            ];

        } catch (Exception $e) {
            $conn->rollBack();
            if (isset($transactionId)) {
                $this->transactionDAO->log($transactionId, 'checkout_erro', ['erro' => $e->getMessage()], 'error');
            }
            throw new Exception("Erro ao iniciar pagamento: " . $e->getMessage());
        }
    }

    public function processarRetorno(string $transactionId, int $usuarioId): array
    {
        $transaction = $this->transactionDAO->findByTransactionId($transactionId);

        if (!$transaction) {
            throw new Exception("Transação não encontrada.");
        }

        $clienteDAO  = new ClienteDAO();
        $cliente     = $clienteDAO->findByUsuarioId($usuarioId);
        $agendamentoDAO = new AgendamentoDAO();
        $agendamento = $agendamentoDAO->findById((int) $transaction['agendamento_id']);

        if (!$cliente || !$agendamento ||
            (int) $agendamento['cliente_id'] !== (int) $cliente['id']) {
            throw new Exception("Acesso negado.");
        }

        if ($transaction['status'] === 'pending' && !empty($transaction['gateway_reference'])) {
            try {
                $gateway    = GatewayFactory::make($transaction['gateway']);
                $statusData = $gateway->getStatus($transaction['gateway_reference']);
                if ($statusData['status'] !== 'pending') {
                    $this->confirmarTransacao($transactionId, $statusData['status'], $statusData['paid_at'] ?? null);
                    $transaction = $this->transactionDAO->findByTransactionId($transactionId);
                }
            } catch (Exception $e) {
                error_log("[PagamentoService] Falha ao consultar gateway: " . $e->getMessage());
            }
        }

        return [
            'status'      => $transaction['status'],
            'transaction' => $transaction,
        ];
    }

    public function processarWebhook(string $gatewayNome, array $payload, array $headers): array
    {
        $webhookId = null;

        try {
            $gateway   = GatewayFactory::make($gatewayNome);
            $webhookId = $this->transactionDAO->logWebhook([
                'gateway'           => $gatewayNome,
                'transaction_id'    => null,
                'gateway_reference' => null,
                'payload'           => $payload,
                'headers'           => $headers,
                'status'            => 'received',
                'processado'        => 0,
            ]);

            $result = $gateway->handleWebhook($payload, $headers);

            if (!$result['valid']) {
                $this->transactionDAO->marcarWebhookErro($webhookId, 'Assinatura inválida ou payload irrelevante');
                return ['processado' => false, 'status' => 'invalid'];
            }

            $transactionId    = $result['transaction_id']    ?? null;
            $gatewayReference = $result['gateway_reference'] ?? null;
            $status           = $result['status'];

            if ($gatewayReference && $this->transactionDAO->webhookJaProcessado($gatewayReference)) {
                $this->transactionDAO->marcarWebhookProcessado($webhookId);
                return ['processado' => true, 'status' => 'already_processed'];
            }

            if (!$transactionId) {
                $this->transactionDAO->marcarWebhookProcessado($webhookId);
                return ['processado' => true, 'status' => 'no_transaction'];
            }

            $this->confirmarTransacao($transactionId, $status, $result['paid_at'] ?? null);
            $this->transactionDAO->marcarWebhookProcessado($webhookId);

            return ['processado' => true, 'status' => $status];

        } catch (Exception $e) {
            if ($webhookId) {
                $this->transactionDAO->marcarWebhookErro($webhookId, $e->getMessage());
            }
            error_log("[PagamentoService::Webhook] Erro: " . $e->getMessage());
            throw $e;
        }
    }

    private function confirmarTransacao(string $transactionId, string $status, ?string $paidAt): void
    {
        $transaction = $this->transactionDAO->findByTransactionId($transactionId);
        if (!$transaction) return;

        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            $this->transactionDAO->updateStatus($transactionId, $status, $paidAt);
            $agendamentoId = (int) $transaction['agendamento_id'];

            if ($status === 'approved') {
                $jaExiste = $this->pagamentoDAO->findByAgendamento($agendamentoId);
                if (!$jaExiste || $jaExiste['status_pagamento'] !== 'pago') {
                    if ($jaExiste) {
                        $conn->prepare(
                            "UPDATE pagamentos SET status_pagamento = 'pago', data_pagamento = NOW()
                             WHERE agendamento_id = ?"
                        )->execute([$agendamentoId]);
                    } else {
                        $this->pagamentoDAO->createPendente([
                            'agendamento_id' => $agendamentoId,
                            'metodo'         => $transaction['gateway'],
                            'valor'          => $transaction['amount'],
                        ]);
                        $conn->prepare(
                            "UPDATE pagamentos SET status_pagamento = 'pago', data_pagamento = NOW()
                             WHERE agendamento_id = ? ORDER BY id DESC LIMIT 1"
                        )->execute([$agendamentoId]);
                    }
                }
                $conn->prepare("UPDATE agendamentos SET status = 'pagamento_aprovado' WHERE id = ?")
                     ->execute([$agendamentoId]);

            } elseif ($status === 'rejected') {
                $conn->prepare("UPDATE agendamentos SET status = 'pagamento_recusado' WHERE id = ?")
                     ->execute([$agendamentoId]);

            } elseif ($status === 'cancelled') {
                $conn->prepare("UPDATE agendamentos SET status = 'pagamento_cancelado' WHERE id = ?")
                     ->execute([$agendamentoId]);

            } elseif ($status === 'refunded') {
                $conn->prepare("UPDATE agendamentos SET status = 'reembolsado' WHERE id = ?")
                     ->execute([$agendamentoId]);
                $pag = $this->pagamentoDAO->findByAgendamento($agendamentoId);
                if ($pag) $this->pagamentoDAO->estornar((int) $pag['id']);
            }

            $this->transactionDAO->log($transactionId, "status_{$status}", [
                'agendamento_id' => $agendamentoId,
                'paid_at'        => $paidAt,
            ]);

            $conn->commit();

        } catch (Exception $e) {
            $conn->rollBack();
            $this->transactionDAO->log($transactionId, 'confirmacao_erro', ['erro' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    // FLUXO MANUAL ADMIN
    // ═══════════════════════════════════════════════════════════════════

    public function pagar(array $dados): bool
    {
        if ((float) $dados['valor'] <= 0) {
            throw new Exception("Valor inválido.");
        }

        $jaExiste = $this->pagamentoDAO->findByAgendamento((int) $dados['agendamento_id']);
        if ($jaExiste && $jaExiste['status_pagamento'] === 'pago') {
            throw new Exception("Este agendamento já possui pagamento confirmado.");
        }

        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            $ok = $this->pagamentoDAO->create([
                'agendamento_id' => $dados['agendamento_id'],
                'metodo'         => $dados['metodo'],
                'valor'          => $dados['valor'],
            ]);
            $conn->prepare("UPDATE agendamentos SET status = 'pagamento_aprovado' WHERE id = ?")
                 ->execute([$dados['agendamento_id']]);
            $conn->commit();
            return $ok;
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Erro ao registrar pagamento manual: " . $e->getMessage());
        }
    }

    public function estornar(int $pagamentoId): array
    {
        $pagamento = $this->pagamentoDAO->findById($pagamentoId);

        if (!$pagamento) throw new Exception("Pagamento não encontrado.");
        if ($pagamento['status_pagamento'] !== 'pago') {
            throw new Exception("Apenas pagamentos confirmados podem ser estornados.");
        }

        $conn = Database::getConnection();
        $conn->beginTransaction();

        try {
            $this->pagamentoDAO->estornar($pagamentoId);
            $conn->prepare("UPDATE agendamentos SET status = 'reembolsado' WHERE id = ?")
                 ->execute([$pagamento['agendamento_id']]);
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollBack();
            throw new Exception("Erro ao estornar: " . $e->getMessage());
        }

        return ['valor' => (float) $pagamento['valor'], 'servico' => $pagamento['servico']];
    }

    // ═══════════════════════════════════════════════════════════════════
    // CONSULTAS
    // ═══════════════════════════════════════════════════════════════════

    public function listarPorCliente(int $usuarioId): array
    {
        return $this->transactionDAO->findByCliente($usuarioId);
    }

    public function listarTodos(array $filtros = []): array
    {
        return $this->transactionDAO->findAll($filtros);
    }

    public function totais(): array
    {
        return $this->transactionDAO->totais();
    }
}
