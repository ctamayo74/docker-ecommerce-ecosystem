<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="StoreOrderRequest",
 *     title="Crear orden de compra",
 *     required={"items"},
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             required={"product_id","quantity"},
 *             @OA\Property(property="product_id", type="integer", example=1),
 *             @OA\Property(property="quantity", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Property(property="shipping_address", type="string", nullable=true, example="Soyapango, San Salvador"),
 *     @OA\Property(property="currency", type="string", nullable=true, example="usd")
 * )
 */
class StoreOrderRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        // El usuario debe estar autenticado (middleware auth:sanctum en la ruta).
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'shipping_address' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'size:3'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debes incluir al menos un producto en la orden.',
            'items.min' => 'Debes incluir al menos un producto en la orden.',
            'items.*.product_id.required' => 'Cada renglón debe indicar el producto.',
            'items.*.product_id.exists' => 'Uno de los productos seleccionados no existe.',
            'items.*.quantity.required' => 'Cada renglón debe indicar la cantidad.',
            'items.*.quantity.min' => 'La cantidad mínima por producto es 1.',
        ];
    }
}
