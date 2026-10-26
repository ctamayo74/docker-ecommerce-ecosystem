<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/orders",
     *     tags={"Órdenes"},
     *     summary="Historial de compras del cliente autenticado",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="per_page", in="query", description="Resultados por página", @OA\Schema(type="integer", default=15)),
     *     @OA\Response(
     *         response=200,
     *         description="Historial de órdenes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Order")),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100) ?: 15;

        $orders = $request->user()
            ->orders()
            ->with(['items', 'payment'])
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * @OA\Post(
     *     path="/orders",
     *     tags={"Órdenes"},
     *     summary="Crear una nueva orden de compra",
     *     description="Crea una orden en estado 'pending' a partir de un carrito de productos. Verifica y descuenta el stock disponible dentro de una transacción de base de datos.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreOrderRequest")),
     *     @OA\Response(response=201, description="Orden creada", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="message", type="string", example="Orden creada exitosamente."),
     *         @OA\Property(property="data", ref="#/components/schemas/Order")
     *     )),
     *     @OA\Response(response=401, description="No autenticado", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=422, description="Error de validación o stock insuficiente", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $order = DB::transaction(function () use ($data, $request) {
            // Se bloquean los renglones de producto (lockForUpdate) para evitar
            // condiciones de carrera al descontar stock con compras simultáneas.
            $productIds = collect($data['items'])->pluck('product_id');
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $total = 0;
            $itemsToInsert = [];

            foreach ($data['items'] as $item) {
                /** @var Product $product */
                $product = $products->get($item['product_id']);

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "El producto con ID {$item['product_id']} no está disponible.",
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para el producto '{$product->name}'. Disponible: {$product->stock}.",
                    ]);
                }

                $subtotal = round($product->price * $item['quantity'], 2);
                $total += $subtotal;

                $itemsToInsert[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock', $item['quantity']);
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_number' => $this->generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'total' => $total,
                'currency' => $data['currency'] ?? 'usd',
                'shipping_address' => $data['shipping_address'] ?? $request->user()->address,
            ]);

            foreach ($itemsToInsert as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'product_name' => $line['product']->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $line['subtotal'],
                ]);
            }

            return $order;
        });

        return response()->json([
            'success' => true,
            'message' => 'Orden creada exitosamente. Procede al pago mediante /api/orders/{order}/pay.',
            'data' => new OrderResource($order->load(['items', 'payment'])),
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/orders/{order}",
     *     tags={"Órdenes"},
     *     summary="Ver el detalle de una orden propia",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="order", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detalle de la orden", @OA\JsonContent(
     *         @OA\Property(property="success", type="boolean", example=true),
     *         @OA\Property(property="data", ref="#/components/schemas/Order")
     *     )),
     *     @OA\Response(response=403, description="No autorizado para ver esta orden", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response=404, description="Orden no encontrada", @OA\JsonContent(ref="#/components/schemas/ErrorResponse"))
     * )
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para ver esta orden.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order->load(['items', 'payment'])),
        ]);
    }

    /**
     * Genera un número de orden único legible, ej. ORD-2026-000123.
     */
    protected function generateOrderNumber(): string
    {
        $year = now()->year;
        $sequence = str_pad((string) (Order::count() + 1), 6, '0', STR_PAD_LEFT);

        $orderNumber = "ORD-{$year}-{$sequence}";

        // Garantiza unicidad incluso si hubo eliminaciones u operaciones concurrentes.
        while (Order::where('order_number', $orderNumber)->exists()) {
            $orderNumber = 'ORD-'.$year.'-'.random_int(100000, 999999);
        }

        return $orderNumber;
    }
}
