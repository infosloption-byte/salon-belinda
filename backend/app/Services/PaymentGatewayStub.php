<?php

namespace App\Services;

/**
 * Stand-in for a real payment gateway (e.g. PayHere, Stripe).
 *
 * No gateway is connected yet. To keep the order cycle testable end-to-end,
 * every card charge attempt here succeeds immediately and returns a mock
 * transaction ID. When you're ready to go live:
 *
 *   1. Add PAYMENT_GATEWAY_KEY / PAYMENT_GATEWAY_SECRET to .env
 *      (see config/services.php).
 *   2. Replace the body of charge() with a real API call to the gateway.
 *   3. Handle the gateway's webhook/callback to update Order::payment_status
 *      asynchronously instead of assuming success inline.
 *
 * Nothing outside this class needs to change — OrderController only talks
 * to this interface.
 */
class PaymentGatewayStub
{
    /**
     * @return array{success: bool, transaction_id: string}
     */
    public function charge(int $amount, array $cardMeta = []): array
    {
        return [
            'success' => true,
            'transaction_id' => 'MOCK-'.strtoupper(bin2hex(random_bytes(5))),
        ];
    }
}
