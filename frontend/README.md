# Tienda — Frontend Next.js 16 para `ecommerce-api-laravel12`

Next.js (App Router) + TypeScript + Tailwind v4. Consume la API Laravel 12 (Sanctum + Stripe).

## Puesta en marcha

```bash
# 1) API Laravel corriendo (ver su README): http://localhost:8000/api
# 2) Frontend
cp .env.example .env.local     # completa NEXT_PUBLIC_STRIPE_KEY (pk_test_...)
npm install
npm run dev                    # http://localhost:3000
```

Credenciales de prueba (seeder de la API): `cliente@example.com` / `password123`.
Tarjeta Stripe de prueba: `4242 4242 4242 4242`, fecha futura, cualquier CVC.

> Requiere Node ≥ 20.9. No hace falta configurar CORS en Laravel: **todas las llamadas a la API
> ocurren en el servidor de Next** (Server Components y Server Actions). El navegador solo habla con Next y con Stripe.js.

## Arquitectura

| Requisito | Dónde |
|---|---|
| Lecturas en servidor (Server Components) | `lib/data.ts` → `app/products/**`, `app/orders/**`, `app/checkout/**` |
| Mutaciones (Server Actions) | `app/actions/auth.ts` (login, registro, logout) · `app/actions/orders.ts` (crear orden, pagar) |
| Token seguro | Cookie **httpOnly + SameSite=Lax + Secure (prod)** creada en la Server Action (`saveToken`). El JS del navegador nunca lo ve; se adjunta como `Bearer` solo en `lib/api.ts` (`server-only`). |
| Protección de rutas | `proxy.ts` (Next 16, reemplaza `middleware.ts`) exige la cookie en `/orders/*` y `/checkout/*`; además `lib/data.ts` redirige a `/login?expired=1` si la API responde 401. |
| Carrito (estado local) | `components/cart-provider.tsx` (useReducer + localStorage, respeta stock) |
| Orden + pago | `/cart` → `createOrder` → `/checkout/[orderId]` → Stripe.js crea `payment_method_id` → `payOrder` → `/orders/[id]?paid=1` |
| `loading.tsx` | `products/`, `products/[id]/`, `orders/`, `checkout/[orderId]/` |
| `error.tsx` | raíz, `orders/`, `checkout/[orderId]/` (+ `not-found.tsx`) |
| `<Suspense>` | Lista de productos (`products/page.tsx`) e historial (`orders/page.tsx`); también el menú de usuario del header |
| Sin UI desactualizada | Tras crear/pagar: `updateTag("products")` (stock cambió) + `revalidatePath("/orders")` y `/orders/[id]` |
| Cache de lecturas públicas | `fetch` con `tags: ["products"]` y `revalidate: 60` (`lib/api.ts`) |

## Supuestos sobre la API

El README de la API no detalla la forma exacta de las respuestas, así que `lib/data.ts` y
`app/actions/*.ts` normalizan variantes comunes de Laravel (`{data: ...}`, `meta.current_page`,
`token` en `token` o `data.token`, `unit_price`/`price`, etc.). Si tu API difiere, **solo hay que ajustar
`toProduct`, `toOrder`, `toPage` y `extractToken`**; el resto de la app no cambia.

Body de registro enviado: `name, email, password, password_confirmation`.

## Limitaciones conocidas

- **3D Secure** (`4000 0025 0000 3155`): la API confirma el PaymentIntent en el servidor; si responde
  `requires_action` habría que devolver el `client_secret` y llamar a `stripe.confirmCardPayment` en el cliente.
  Para el flujo del curso usa `4242…`.
- El historial no pagina en UI (muestra la primera página que devuelve la API).

## Evidencia de rendimiento

Ver [`docs/LIGHTHOUSE.md`](docs/LIGHTHOUSE.md).
