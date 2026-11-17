<?php

/**
 * PaymentGatewayInterface — contrato para todos os gateways de pagamento.
 *
 * Permite trocar gateway (MercadoPago → PagSeguro → Stripe) sem
 * alterar nenhuma regra de negócio no PagamentoService.
 */
interface PaymentGatewayInterface
{
    /**
     * Cria uma preferência/sessão de pagamento no gateway externo.
     *
     * @param array $dados {
     *   agendamento_id: int,
     *   transaction_id: string,   // ID interno da nossa tabela payment_transactions
     *   valor: float,
     *   descricao: string,
     *   cliente_nome: string,
     *   cliente_email: string,
     *   url_sucesso: string,
     *   url_pendente: string,
     *   url_falha: string,
     *   url_webhook: string,
     * }
     * @return array {
     *   checkout_url: string,     // URL para redirecionar o cliente
     *   gateway_reference: string, // ID/preferência gerada pelo gateway
     * }
     * @throws Exception em caso de falha de comunicação
     */
    public function createCheckout(array $dados): array;

    /**
     * Consulta o status de uma transação diretamente no gateway.
     *
     * @param string $gatewayReference  ID retornado pelo gateway
     * @return array { status: string, paid_at: string|null, raw: array }
     */
    public function getStatus(string $gatewayReference): array;

    /**
     * Valida e interpreta o payload recebido via webhook.
     *
     * @param array  $payload    Dados recebidos (json decodificado)
     * @param array  $headers    Cabeçalhos HTTP da requisição
     * @return array {
     *   valid: bool,
     *   transaction_id: string|null,
     *   gateway_reference: string|null,
     *   status: string,           // approved | pending | rejected | cancelled | refunded
     *   paid_at: string|null,
     *   raw: array
     * }
     */
    public function handleWebhook(array $payload, array $headers): array;

    /**
     * Retorna o identificador textual do gateway (ex: 'mercadopago').
     */
    public function getName(): string;
}
