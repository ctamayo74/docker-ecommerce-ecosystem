<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="UpdateProductRequest",
 *     title="Actualizar producto",
 *     @OA\Property(property="name", type="string", example="Teclado mecánico RGB Pro"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Switches azules, retroiluminado"),
 *     @OA\Property(property="sku", type="string", example="TEC-MEC-001"),
 *     @OA\Property(property="price", type="number", format="float", example=54.99),
 *     @OA\Property(property="stock", type="integer", example=40),
 *     @OA\Property(property="image_url", type="string", nullable=true),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class UpdateProductRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['sometimes', 'required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($productId)],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'required', 'integer', 'min:0'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Ya existe un producto con este SKU.',
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser negativo.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'image_url.url' => 'La URL de la imagen no es válida.',
        ];
    }
}
