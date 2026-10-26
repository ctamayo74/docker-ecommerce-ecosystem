<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Ecommerce API - UCAD",
 *     description="API RESTful para la gestión de clientes, catálogo de productos y procesamiento de compras mediante Stripe. Construida con Laravel 12, autenticación por tokens (Sanctum) y documentada con OpenAPI 3.0.",
 *     @OA\Contact(name="Soporte API", email="soporte@example.com"),
 *     @OA\License(name="MIT", url="https://opensource.org/licenses/MIT")
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Servidor de la API"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Token",
 *     description="Autenticación mediante Bearer Token emitido por /api/auth/login o /api/auth/register (Laravel Sanctum)."
 * )
 *
 * @OA\Tag(name="Autenticación", description="Registro, login y logout de clientes")
 * @OA\Tag(name="Productos", description="Catálogo de productos (CRUD)")
 * @OA\Tag(name="Órdenes", description="Creación y consulta de órdenes de compra")
 * @OA\Tag(name="Pagos", description="Procesamiento de pagos con Stripe")
 *
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     title="Respuesta de error",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Ha ocurrido un error."),
 *     @OA\Property(property="errors", type="object", nullable=true)
 * )
 */
abstract class Controller
{
    //
}
