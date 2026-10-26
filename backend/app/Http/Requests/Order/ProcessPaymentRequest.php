<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="ProcessPaymentRequest",
 *     title="Procesar pago de una orden",
 *     required={"payment_method_id"},
 *     description="payment_method_id es el identificador generado por Stripe.js / Stripe Elements en el frontend (ej. pm_card_visa en modo prueba).",
 *     @OA\Property(property="payment_method_id", type="string", example="pm_card_visa")
 * )
 */
class ProcessPaymentRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_method_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Debes indicar el método de pago generado por Stripe.',
        ];
    }
}
