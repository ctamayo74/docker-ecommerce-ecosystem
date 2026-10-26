<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ProcessPaymentRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;

class PaymentController extends Controller
{
    public function __construct(protected StripeService $stripeService) {}

    /**
     * @OA\Post(
     *     path="/orders/{order}/pay",
     *     tags={"Pagos"},
     *     summary="Procesar el pago de una orden mediante Stripe",
     *     description="Crea y confirma un PaymentIntent de Stripe para la orden indicada, usando el payment_method_id generado en el frontend (Stripe.js / Stripe Elements). Actualiza el estado de la orden según el resultado.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ProcessPaymentRequest")),
     *     @OA\Response(response=200, description="Pago procesado", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Pago procesado exitosamente."),
     *         @OA\Property(property="data", ref="#/components/schemas/Order")
     *     )),
     *     @OA\Response(response=402, description="El pago fue rechazado por Stripe", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=403, description="No autorizado para pagar esta orden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=409, description="La orden ya fue pagada o no está pendiente", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Error de validación", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function pay(ProcessPaymentRequest $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para pagar esta orden.',
            ], 403);
        }

        if ($order->status !== Order::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => "La orden no se puede pagar porque su estado actual es '{$order->status}'.",
            ], 409);
        }

        try {
            $paymentIntent = $this->stripeService->createAndConfirmPaymentIntent(
                $order,
                $request->validated('payment_method_id')
            );
        } catch (ApiErrorException $e) {
            Log::warning('Stripe payment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            $order->update(['status' => Order::STATUS_FAILED]);

            return response()->json([
                'success' => false,
                'message' => 'El pago fue rechazado por la pasarela de pago: '.$e->getMessage(),
            ], 402);
        }

        $order = DB::transaction(function () use ($order, $paymentIntent) {
            $succeeded = $paymentIntent->status === 'succeeded';

            Payment::updateOrCreate(
                ['stripe_payment_intent_id' => $paymentIntent->id],
                [
                    'order_id' => $order->id,
                    'stripe_charge_id' => $paymentIntent->latest_charge ?? null,
                    'amount' => $order->total,
                    'currency' => $order->currency,
                    'status' => $paymentIntent->status,
                    'payment_method' => $paymentIntent->payment_method,
                    'raw_response' => $paymentIntent->toArray(),
                    'paid_at' => $succeeded ? now() : null,
                ]
            );

            $order->update([
                'status' => $succeeded ? Order::STATUS_PAID : Order::STATUS_FAILED,
            ]);

            return $order;
        });

        if ($order->status !== Order::STATUS_PAID) {
            return response()->json([
                'success' => false,
                'message' => 'El pago no pudo completarse. Estado de Stripe: '.$paymentIntent->status,
                'data' => new OrderResource($order->load(['items', 'payment'])),
            ], 402);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pago procesado exitosamente.',
            'data' => new OrderResource($order->load(['items', 'payment'])),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/webhooks/stripe",
     *     tags={"Pagos"},
     *     summary="Webhook de Stripe",
     *     description="Endpoint público (sin autenticación de usuario) que recibe eventos asíncronos de Stripe (ej. confirmaciones de pago posteriores, disputas, reembolsos) validando la firma del webhook.",
     *     @OA\Response(response=200, description="Evento procesado"),
     *     @OA\Response(response=400, description="Firma de webhook inválida")
     * )
     *
     * No requiere autenticación de usuario: la seguridad se garantiza
     * verificando la firma criptográfica enviada por Stripe en el
     * encabezado `Stripe-Signature` (ver StripeService::constructWebhookEvent).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (SignatureVerificationException|\UnexpectedValueException $e) {
            Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'Firma inválida.'], 400);
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
            case 'payment_intent.payment_failed':
                $intent = $event->data->object;
                $payment = Payment::where('stripe_payment_intent_id', $intent->id)->first();

                if ($payment) {
                    $succeeded = $event->type === 'payment_intent.succeeded';

                    $payment->update([
                        'status' => $intent->status,
                        'raw_response' => $intent->toArray(),
                        'paid_at' => $succeeded ? now() : $payment->paid_at,
                    ]);

                    $payment->order->update([
                        'status' => $succeeded ? Order::STATUS_PAID : Order::STATUS_FAILED,
                    ]);
                }
                break;

            default:
                Log::info('Evento de Stripe no manejado explícitamente', ['type' => $event->type]);
        }

        return response()->json(['success' => true]);
    }
}
