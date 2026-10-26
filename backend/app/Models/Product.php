<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Producto",
 *     description="Producto disponible en el catálogo de la tienda",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Teclado mecánico RGB"),
 *     @OA\Property(property="description", type="string", example="Teclado mecánico switches rojos, retroiluminado"),
 *     @OA\Property(property="sku", type="string", example="TEC-MEC-001"),
 *     @OA\Property(property="price", type="number", format="float", example=49.99),
 *     @OA\Property(property="stock", type="integer", example=25),
 *     @OA\Property(property="image_url", type="string", nullable=true, example="https://example.com/images/teclado.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'price',
        'stock',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Renglones de orden en los que aparece este producto.
     *
     * @return HasMany<OrderItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope para filtrar únicamente productos activos y con stock.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true);
    }
}
