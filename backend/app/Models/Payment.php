<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     title="Pago",
 *     description="Registro de una transacción procesada mediante Stripe",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_id", type="integer", example=10),
 *     @OA\Property(property="stripe_payment_intent_id", type="string", example="pi_3Nk...abc"),
 *     @OA\Property(property="stripe_charge_id", type="string", nullable=true, example="ch_3Nk...abc"),
 *     @OA\Property(property="amount", type="number", format="float", example=99.98),
 *     @OA\Property(property="currency", type="string", example="usd"),
 *     @OA\Property(property="status", type="string", enum={"requires_payment_method","requires_action","processing","succeeded","failed","canceled"}, example="succeeded"),
 *     @OA\Property(property="payment_method", type="string", nullable=true, example="card"),
 *     @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    public const STATUS_REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    public const STATUS_REQUIRES_ACTION = 'requires_action';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELED = 'canceled';

    protected $fillable = [
        'order_id',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'raw_response',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'raw_response' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, Payment>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
