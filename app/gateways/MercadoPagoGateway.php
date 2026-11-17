<?php

/**
 * MercadoPagoGateway — integração com a API v2 do Mercado Pago.
 *
 * Documentação: https://www.mercadopago.com.br/developers/pt/docs
 *
 * Configuração necessária (config/pagamento.php ou variáveis de ambiente):
 *   MERCADOPAGO_ACCESS_TOKEN  → token de produção ou sandbox
 *   MERCADOPAGO_WEBHOOK_SECRET → chave para validar assinatura do webhook
 */
class MercadoPagoGateway implements PaymentGatewayInterface
{
    private string $accessToken;
    private string $webhookSecret;
    private string $baseUrl = 'https://api.mercadopago.com';

    public function __construct()
    {
        $this->accessToken   = defined('MP_ACCESS_TOKEN')   ? MP_ACCESS_TOKEN   : (getenv('MERCADOPAGO_ACCESS_TOKEN')   ?: '');
        $this->webhookSecret = defined('MP_WEBHOOK_SECRET') ? MP_WEBHOOK_SECRET : (getenv('MERCADOPAGO_WEBHOOK_SECRET') ?: '');

        if (empty($this->accessToken)) {
            throw new Exception('MercadoPago: ACCESS_TOKEN não configurado. Defina MP_ACCESS_TOKEN em config/pagamento.php');
        }
    }

    // ─────────────────────────────────────────────────────────────────
    // Cria preferência de pagamento (Checkout Pro)
    // ─────────────────────────────────────────────────────────────────
    public function createCheckout(array $dados): array
    {
        $payload = [
            'external_reference' => $dados['transaction_id'],
            'items' => [
                [
                    'id'          => (string) $dados['agendamento_id'],
                    'title'       => $dados['descricao'],
                    'description' => 'Serviço Afro Essência',
                    'quantity'    => 1,
                    'currency_id' => 'BRL',
                    'unit_price'  => (float) $dados['valor'],
                ]
            ],
            'payer' => [
                'name'  => $dados['cliente_nome'],
                'email' => $dados['cliente_email'],
            ],
            'back_urls' => [
                'success' => $dados['url_sucesso'],
                'pending' => $dados['url_pendente'],
                'failure' => $dados['url_falha'],
            ],
            'auto_return'          => 'approved',
            'notification_url'     => $dados['url_webhook'],
            'statement_descriptor' => 'AFROESSENCIA',
            'expires'              => true,
            'expiration_date_from' => gmdate('Y-m-d\TH:i:s.000\Z'),
            'expiration_date_to'   => gmdate('Y-m-d\TH:i:s.000\Z', strtotime('+24 hours')),
        ];

        $response = $this->request('POST', '/checkout/preferences', $payload);

        if (empty($response['id'])) {
            throw new Exception('MercadoPago: falha ao criar preferência. ' . json_encode($response));
        }

        // Detecta automaticamente sandbox vs produção pelo token
        // Tokens de produção começam com 'APP_USR-'; de teste com 'TEST-'
        $isSandbox   = str_starts_with($this->accessToken, 'TEST-');
        $checkoutUrl = $isSandbox
            ? ($response['sandbox_init_point'] ?? $response['init_point'])
            : ($response['init_point'] ?? $response['sandbox_init_point']);

        return [
            'checkout_url'      => $checkoutUrl,
            'gateway_reference' => $response['id'],
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Consulta status de um pagamento por gateway_reference
    // ─────────────────────────────────────────────────────────────────
    public function getStatus(string $gatewayReference): array
    {
        // gateway_reference aqui pode ser o payment_id ou preference_id
        // Buscamos o pagamento pelo external_reference para maior confiabilidade
        $response = $this->request('GET', "/v1/payments/search?external_reference={$gatewayReference}");

        $status  = 'pending';
        $paidAt  = null;
        $rawData = $response;

        if (!empty($response['results'][0])) {
            $payment = $response['results'][0];
            $status  = $this->normalizeStatus($payment['status'] ?? 'pending', $payment['status_detail'] ?? '');
            $paidAt  = $payment['date_approved'] ?? null;
        }

        return [
            'status'  => $status,
            'paid_at' => $paidAt,
            'raw'     => $rawData,
        ];
    }

    // ─────────────────────────────────────────────────────────────────
    // Processa webhook recebido
    // ─────────────────────────────────────────────────────────────────
    public function handleWebhook(array $payload, array $headers): array
    {
        $result = [
            'valid'             => false,
            'transaction_id'    => null,
            'gateway_reference' => null,
            'status'            => 'pending',
            'paid_at'           => null,
            'raw'               => $payload,
        ];

        // ── Valida assinatura (x-signature) ──────────────────────────
        if (!empty($this->webhookSecret)) {
            if (!$this->validateSignature($payload, $headers)) {
                return $result; // assinatura inválida
            }
        }

        // ── Tipo de notificação ───────────────────────────────────────
        $type   = $payload['type']   ?? $payload['topic'] ?? '';
        $dataId = $payload['data']['id'] ?? $payload['id'] ?? null;

        if ($type !== 'payment' || !$dataId) {
            // IPN de outros tipos (merchant_orders, etc.) — ignoramos
            $result['valid'] = true; // válida mas irrelevante
            return $result;
        }

        // ── Busca dados reais do pagamento no MP ──────────────────────
        $payment = $this->request('GET', "/v1/payments/{$dataId}");

        if (empty($payment['id'])) {
            return $result;
        }

        $result['valid']             = true;
        $result['gateway_reference'] = (string) $payment['id'];
        $result['transaction_id']    = $payment['external_reference'] ?? null;
        $result['status']            = $this->normalizeStatus($payment['status'] ?? '', $payment['status_detail'] ?? '');
        $result['paid_at']           = $payment['date_approved'] ?? null;
        $result['raw']               = $payment;

        return $result;
    }

    public function getName(): string
    {
        return 'mercadopago';
    }

    // ─────────────────────────────────────────────────────────────────
    // Helpers privados
    // ─────────────────────────────────────────────────────────────────

    private function normalizeStatus(string $mpStatus, string $detail = ''): string
    {
        switch ($mpStatus) {
            case 'approved':
                return 'approved';
            case 'pending':
            case 'in_process':
            case 'authorized':
                return 'pending';
            case 'rejected':
                return 'rejected';
            case 'cancelled':
                return 'cancelled';
            case 'refunded':
            case 'charged_back':
                return 'refunded';
            default:
                return 'pending';
        }
    }

    private function validateSignature(array $payload, array $headers): bool
    {
        // Mercado Pago v2 envia x-signature: ts=...;v1=...
        $sig     = $headers['x-signature'] ?? $headers['X-Signature'] ?? '';
        $reqId   = $headers['x-request-id'] ?? $headers['X-Request-Id'] ?? '';
        $dataId  = $payload['data']['id'] ?? '';

        if (empty($sig)) {
            return true; // sem secret configurado não valida
        }

        preg_match('/ts=(\d+)/', $sig, $tsMatch);
        preg_match('/v1=([a-f0-9]+)/', $sig, $hashMatch);

        $ts   = $tsMatch[1]   ?? '';
        $hash = $hashMatch[1] ?? '';

        $manifest = "id:{$dataId};request-id:{$reqId};ts:{$ts};";
        $expected = hash_hmac('sha256', $manifest, $this->webhookSecret);

        return hash_equals($expected, $hash);
    }

    private function request(string $method, string $endpoint, array $body = []): array
    {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->accessToken,
                'Content-Type: application/json',
                'X-Idempotency-Key: ' . uniqid('ae_', true),
            ],
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $raw    = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new Exception("MercadoPago cURL error: {$err}");
        }

        $data = json_decode($raw, true) ?? [];

        if ($status >= 400) {
            throw new Exception("MercadoPago API error {$status}: " . ($data['message'] ?? $raw));
        }

        return $data;
    }
}
