<?php

/**
 * StripeGateway — estrutura preparada para implementação futura.
 *
 * Para ativar:
 *  1. Defina STRIPE_SECRET_KEY e STRIPE_WEBHOOK_SECRET em config/pagamento.php
 *  2. Implemente os métodos usando a SDK oficial do Stripe ou a API REST
 *  3. Altere GatewayFactory para retornar esta classe quando gateway = 'stripe'
 */
class StripeGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $dados): array
    {
        throw new Exception('Stripe: gateway não implementado. Configure as credenciais e implemente a integração.');
    }

    public function getStatus(string $gatewayReference): array
    {
        throw new Exception('Stripe: gateway não implementado.');
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        throw new Exception('Stripe: gateway não implementado.');
    }

    public function getName(): string
    {
        return 'stripe';
    }
}
