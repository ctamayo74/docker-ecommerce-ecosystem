<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Todas las rutas aquí definidas se registran automáticamente bajo el
| prefijo /api (ver bootstrap/app.php) y usan el guard "sanctum" para
| autenticación por tokens.
|
*/

// -----------------------------------------------------------------------
// Autenticación (públicas + protegidas)
// -----------------------------------------------------------------------
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// -----------------------------------------------------------------------
// Catálogo de productos
// Listado y detalle son públicos; creación, edición y eliminación
// requieren autenticación (ver requisito 6: "CRUD completo de productos
// con autenticación para crear, editar y eliminar").
// -----------------------------------------------------------------------
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

// -----------------------------------------------------------------------
// Órdenes de compra y pagos (siempre requieren autenticación)
// -----------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/pay', [PaymentController::class, 'pay']);
});

// -----------------------------------------------------------------------
// Webhook de Stripe (público, protegido por verificación de firma)
// -----------------------------------------------------------------------
Route::post('/webhooks/stripe', [PaymentController::class, 'webhook']);
