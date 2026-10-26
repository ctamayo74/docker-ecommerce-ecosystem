# Ecommerce API — Laravel 12

API RESTful para la gestión de un e-commerce básico: clientes, catálogo de
productos y procesamiento de compras mediante **Stripe**. Construida con
**Laravel 12**, **PHP 8.2+**, autenticación por tokens (**Laravel Sanctum**),
base de datos **MySQL** y documentación **Swagger / OpenAPI** (paquete
`darkaonline/l5-swagger`).

---

## 1. Requisitos previos

| Herramienta | Versión mínima |
|---|---|
| PHP | 8.2 |
| Composer | 2.x |
| MySQL | 8.0 (o MariaDB 10.6+) |
| Extensiones PHP | `pdo_mysql`, `mbstring`, `openssl`, `json`, `curl`, `bcmath` |
| Cuenta de Stripe (modo test) | — |

> Puedes verificar tus extensiones con `php -m`.

---

## 2. Instalación paso a paso

```bash
# 1. Clonar / descomprimir el proyecto
cd ecommerce-api

# 2. Instalar dependencias PHP
composer install

# 3. Copiar el archivo de entorno y generar la llave de la aplicación
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en .env (ver sección 3)

# 5. Crear la base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE ecommerce_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Configurar tus llaves de Stripe en .env (ver sección 4)

# 8. Generar la documentación Swagger
php artisan l5-swagger:generate

# 9. Levantar el servidor de desarrollo
php artisan serve
```

La API quedará disponible en `http://localhost:8000/api` y la documentación
interactiva Swagger UI en:

```
http://localhost:8000/api/documentation
```

---

## 3. Configuración de la base de datos (`.env`)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_api
DB_USERNAME=root
DB_PASSWORD=tu_password
```

---

## 4. Configuración de Stripe (`.env`)

1. Crea una cuenta gratuita en [https://dashboard.stripe.com/register](https://dashboard.stripe.com/register).
2. En el dashboard, activa el **modo de prueba (Test mode)**.
3. Ve a **Developers → API keys** y copia tu `Publishable key` y `Secret key`.
4. Pégalas en tu `.env`:

```env
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=
STRIPE_CURRENCY=usd
```

### Probar pagos localmente (webhook)

Para recibir eventos de Stripe (confirmaciones asíncronas, disputas, etc.)
en tu entorno local, usa el [Stripe CLI](https://stripe.com/docs/stripe-cli):

```bash
stripe login
stripe listen --forward-to localhost:8000/api/webhooks/stripe
```

El comando anterior imprime un `whsec_...` temporal: úsalo como
`STRIPE_WEBHOOK_SECRET` mientras pruebas localmente.

### Tarjetas de prueba de Stripe

| Número | Resultado |
|---|---|
| `4242 4242 4242 4242` | Pago exitoso |
| `4000 0000 0000 9995` | Fondos insuficientes (rechazado) |
| `4000 0025 0000 3155` | Requiere autenticación 3D Secure |

En el flujo de esta API, el frontend (usando **Stripe.js / Stripe Elements**
o el SDK móvil de Stripe) es responsable de convertir los datos de la
tarjeta en un `payment_method_id` (ej. `pm_card_visa` en modo prueba con
tarjetas predefinidas). Ese identificador es lo que se envía al endpoint
`POST /api/orders/{order}/pay`. La API **nunca** recibe ni almacena números
de tarjeta reales, cumpliendo así con las buenas prácticas de PCI-DSS.

---

## 5. Datos de prueba (seeders)

El comando `php artisan migrate --seed` crea:

- Un cliente de prueba:
  - **Email:** `cliente@example.com`
  - **Password:** `password123`
- Un catálogo de 12 productos de ejemplo (`database/seeders/ProductSeeder.php`),
  incluyendo casos de prueba como un producto sin stock y un producto
  inactivo (para validar el filtrado del catálogo público).

Para re-poblar solo los productos:

```bash
php artisan db:seed --class=ProductSeeder
```

---

## 6. Flujo general de la API

1. **Registro / Login** — `POST /api/auth/register` o `POST /api/auth/login`
   devuelven un `token` (Bearer Token de Sanctum).
2. **Explorar catálogo** — `GET /api/products` (público, con paginación y
   búsqueda por nombre vía `?search=`).
3. **Crear una orden** — `POST /api/orders` (requiere autenticación) con la
   lista de `items` (`product_id` + `quantity`). La API valida stock,
   descuenta el inventario dentro de una transacción de base de datos y
   crea la orden en estado `pending`.
4. **Pagar la orden** — `POST /api/orders/{order}/pay` con el
   `payment_method_id` generado por Stripe en el frontend. La API crea y
   confirma un `PaymentIntent`, y actualiza el estado de la orden a `paid`
   o `failed` según el resultado.
5. **Historial de compras** — `GET /api/orders` devuelve únicamente las
   órdenes del cliente autenticado.

### Autenticación en Swagger UI / Postman

Todas las rutas protegidas usan el header:

```
Authorization: Bearer {token}
```

En Swagger UI, haz clic en **Authorize** e ingresa el token obtenido del
login/registro (sin el prefijo `Bearer`, el propio esquema lo añade).

---

## 7. Endpoints principales

| Método | Ruta | Autenticación | Descripción |
|---|---|---|---|
| POST | `/api/auth/register` | No | Registrar cliente |
| POST | `/api/auth/login` | No | Iniciar sesión |
| POST | `/api/auth/logout` | Sí | Cerrar sesión (revoca el token) |
| GET | `/api/auth/me` | Sí | Perfil del cliente autenticado |
| GET | `/api/products` | No | Listado del catálogo (paginado) |
| GET | `/api/products/{id}` | No | Detalle de un producto |
| POST | `/api/products` | Sí | Crear producto |
| PUT/PATCH | `/api/products/{id}` | Sí | Actualizar producto |
| DELETE | `/api/products/{id}` | Sí | Eliminar producto (soft delete) |
| GET | `/api/orders` | Sí | Historial de compras del cliente |
| POST | `/api/orders` | Sí | Crear una orden de compra |
| GET | `/api/orders/{id}` | Sí | Detalle de una orden propia |
| POST | `/api/orders/{id}/pay` | Sí | Procesar el pago con Stripe |
| POST | `/api/webhooks/stripe` | Firma Stripe | Webhook de eventos de Stripe |

La documentación completa e interactiva (con esquemas de request/response,
ejemplos y posibilidad de "Try it out") está disponible en
**`/api/documentation`**.

---

## 8. Manejo de errores

Todas las respuestas de error de la API siguen un formato JSON consistente:

```json
{
  "success": false,
  "message": "Los datos proporcionados no son válidos.",
  "errors": {
    "email": ["El correo electrónico ya está en uso."]
  }
}
```

Códigos HTTP utilizados: `401` (no autenticado), `403` (no autorizado),
`404` (no encontrado), `409` (conflicto de estado, ej. orden ya pagada),
`422` (validación), `402` (pago rechazado por Stripe), `500` (error
inesperado del servidor).

---

## 9. Ejecutar las pruebas automatizadas

El proyecto incluye pruebas de integración (`tests/Feature/EcommerceFlowTest.php`)
que cubren registro, catálogo, creación de órdenes con control de stock y
autorización sobre órdenes ajenas. Se ejecutan sobre SQLite en memoria, sin
afectar tu base de datos MySQL:

```bash
php artisan test
```

---

## 10. Estructura del proyecto

```
app/
  Http/
    Controllers/Api/     -> AuthController, ProductController, OrderController, PaymentController
    Requests/             -> Form Requests con validación (Auth, Product, Order)
    Resources/             -> Transformadores de respuesta (API Resources)
    Middleware/            -> ForceJsonResponse
  Models/                  -> User, Product, Order, OrderItem, Payment
  Services/                -> StripeService (integración con Stripe)
database/
  migrations/              -> Esquema completo de la base de datos
  seeders/                 -> DatabaseSeeder, ProductSeeder
  factories/               -> UserFactory, ProductFactory (para tests)
routes/
  api.php                  -> Todas las rutas de la API
config/
  l5-swagger.php           -> Configuración de Swagger/OpenAPI
  services.php             -> Credenciales de Stripe
tests/Feature/             -> Pruebas de integración del flujo de compra
```

---

## 11. Notas de arquitectura y buenas prácticas aplicadas

- **Separación de responsabilidades:** la lógica de Stripe vive en
  `App\Services\StripeService`, no en el controlador.
- **Form Requests** para toda validación de entrada, con mensajes en
  español y respuestas JSON uniformes (`App\Http\Requests\BaseApiRequest`).
- **API Resources** para desacoplar la estructura de la base de datos de la
  respuesta pública de la API.
- **Transacciones + bloqueo de filas (`lockForUpdate`)** al crear órdenes,
  para evitar condiciones de carrera al descontar stock con compras
  concurrentes.
- **Soft deletes** en productos, para conservar el historial de órdenes
  aunque un producto se elimine del catálogo.
- **Verificación de firma de webhook** de Stripe (`Stripe-Signature`), en
  vez de confiar ciegamente en el payload recibido.
- **Manejo global de excepciones** (`bootstrap/app.php`) que garantiza que
  cualquier error no controlado explícitamente también responda en JSON.

---

## 12. Solución de problemas comunes

- **`SQLSTATE[HY000] [2002] Connection refused`** → Verifica que MySQL esté
  corriendo y que `DB_HOST`/`DB_PORT` en `.env` sean correctos.
- **`/api/documentation` muestra un esquema vacío o desactualizado** →
  Ejecuta `php artisan l5-swagger:generate` de nuevo tras modificar
  anotaciones `@OA\...`.
- **Error 401 en rutas protegidas** → Verifica que estás enviando el header
  `Authorization: Bearer {token}` con un token válido obtenido de
  `/api/auth/login` o `/api/auth/register`.
- **El pago falla con "No such payment_method"** → En modo prueba, usa un
  `payment_method_id` generado por Stripe.js/Stripe Elements con una de las
  tarjetas de prueba, no un número de tarjeta crudo.

---

## Licencia

Proyecto desarrollado con fines académicos (Kodigo). Distribuido bajo licencia MIT.
