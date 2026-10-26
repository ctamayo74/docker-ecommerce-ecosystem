<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseApiRequest;

/**
 * @OA\Schema(
 *     schema="StoreProductRequest",
 *     title="Crear producto",
 *     required={"name","sku","price","stock"},
 *     @OA\Property(property="name", type="string", example="Teclado mecánico RGB"),
 *     @OA\Property(property="description", type="string", nullable=true, example="Switches rojos, retroiluminado"),
 *     @OA\Property(property="sku", type="string", example="TEC-MEC-001"),
 *     @OA\Property(property="price", type="number", format="float", example=49.99),
 *     @OA\Property(property="stock", type="integer", example=25),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/teclado.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class StoreProductRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        // Solo usuarios autenticados llegan aquí (protegido por middleware auth:sanctum en la ruta).
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
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
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un valor numérico.',
            'price.min' => 'El precio no puede ser negativo.',
            'stock.required' => 'El stock es obligatorio.',
            'stock.integer' => 'El stock debe ser un número entero.',
            'image_url.url' => 'La URL de la imagen no es válida.',
        ];
    }
}
