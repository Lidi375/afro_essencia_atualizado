<?php

/**
 * GatewayFactory — instancia o gateway correto com base na configuração.
 *
 * Para trocar de gateway no futuro, basta alterar PAYMENT_GATEWAY
 * em config/pagamento.php. Nenhuma outra classe precisa ser alterada.
 */
class GatewayFactory
{
    public static function make(string $gateway = ''): PaymentGatewayInterface
    {
        if (empty($gateway)) {
            $gateway = defined('PAYMENT_GATEWAY') ? PAYMENT_GATEWAY : 'mercadopago';
        }

        switch (strtolower($gateway)) {
            case 'mercadopago':
                return new MercadoPagoGateway();
            case 'pagseguro':
                return new PagSeguroGateway();
            case 'stripe':
                return new StripeGateway();
            default:
                throw new Exception("Gateway de pagamento desconhecido: {$gateway}");
        }
    }
}
