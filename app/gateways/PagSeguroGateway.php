<?php

/**
 * PagSeguroGateway — estrutura preparada para implementação futura.
 *
 * Para ativar:
 *  1. Defina PAGSEGURO_TOKEN e PAGSEGURO_EMAIL em config/pagamento.php
 *  2. Implemente os métodos conforme a API v4 do PagSeguro
 *  3. Altere GatewayFactory para retornar esta classe quando gateway = 'pagseguro'
 */
class PagSeguroGateway implements PaymentGatewayInterface
{
    public function createCheckout(array $dados): array
    {
        throw new Exception('PagSeguro: gateway não implementado. Configure as credenciais e implemente a integração.');
    }

    public function getStatus(string $gatewayReference): array
    {
        throw new Exception('PagSeguro: gateway não implementado.');
    }

    public function handleWebhook(array $payload, array $headers): array
    {
        throw new Exception('PagSeguro: gateway não implementado.');
    }

    public function getName(): string
    {
        return 'pagseguro';
    }
}
