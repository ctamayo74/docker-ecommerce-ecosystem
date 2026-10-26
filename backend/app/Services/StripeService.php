<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;

/**
 * Encapsula toda la comunicación con la API de Stripe para que los
 * controladores no dependan directamente del SDK. Facilita pruebas y
 * mantenimiento (principio de responsabilidad única).
 */
class StripeService
{
    protected StripeClient $client;

    public function __construct()
    {
        $this->client = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crea (o reutiliza) un PaymentIntent para una orden y confirma el pago
     * inmediatamente usando el payment_method enviado desde el frontend.
     *
     * @throws ApiErrorException
     */
    public function createAndConfirmPaymentIntent(Order $order, string $paymentMethodId): PaymentIntent
    {
        // El monto se envía a Stripe en la unidad mínima de la moneda
        // (centavos para USD), por lo que se multiplica por 100.
        $amountInCents = (int) round($order->total * 100);

        return $this->client->paymentIntents->create([
            'amount' => $amountInCents,
            'currency' => $order->currency ?? config('services.stripe.currency', 'usd'),
            'payment_method' => $paymentMethodId,
            'confirmation_method' => 'manual',
            'confirm' => true,
            // Evita que Stripe requiera un flujo de redirección (3DS con return_url)
            // para simplificar la demo; en producción se recomienda manejar
            // 'requires_action' con Stripe Elements en el frontend.
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
            'description' => "Orden {$order->order_number}",
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $order->user_id,
            ],
        ]);
    }

    /**
     * Recupera un PaymentIntent existente desde Stripe.
     *
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->client->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Construye y valida un evento de webhook de Stripe a partir del payload
     * crudo y la firma enviada en el encabezado `Stripe-Signature`.
     *
     * @throws \Stripe\Exception\SignatureVerificationException
     * @throws \UnexpectedValueException
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            config('services.stripe.webhook_secret')
        );
    }
}
