# Evidencia de rendimiento (Lighthouse)

> Rellena esta guía con tus capturas reales: Lighthouse debe medirse sobre **tu** build.

## 1. Medir sobre build de producción (nunca `next dev`)

```bash
npm run build && npm run start     # http://localhost:3000
```

1. Abre Chrome en **ventana de incógnito** (sin extensiones).
2. DevTools → pestaña **Lighthouse** → modo *Navigation*, dispositivo *Mobile*, todas las categorías.
3. Ejecuta sobre cada ruta y guarda la captura en `docs/lighthouse/`:

| Ruta | Archivo de evidencia |
|---|---|
| `/products` | `docs/lighthouse/products.png` |
| `/products/1` | `docs/lighthouse/product-detail.png` |
| `/login` | `docs/lighthouse/login.png` |
| `/orders` (con sesión) | `docs/lighthouse/orders.png` |

Alternativa por CLI (genera HTML + JSON adjuntables):

```bash
npx lighthouse http://localhost:3000/products --preset=desktop --output=html --output=json --output-path=docs/lighthouse/products
```

## 2. Resultados (completar)

| Ruta | Perf | A11y | Best Pr. | SEO | LCP | CLS | TBT |
|---|---|---|---|---|---|---|---|
| /products |48| 95| 96| 92| 7.3s| 0| 2,250ms|
| /products/[id] |70 | 96| 96| 100| 1.3s| 0|2,490ms |
| /login |76 | 100| 100| 100| 1.3s|0 |1,190ms |
| /orders |57 | 96| 100| 100| 6.7s|0 | 880ms|

Objetivos orientativos: LCP < 2.5 s, CLS < 0.1, TBT < 200 ms.

## 3. Decisiones de rendimiento ya aplicadas (para tu análisis)

- **Streaming con `<Suspense>` + `loading.tsx`**: el shell se envía antes de que responda la API.
- **Lecturas en Server Components**: no se envía JS para renderizar catálogo/historial.
- **JS de cliente mínimo**: solo carrito, formularios y Stripe; Stripe se carga únicamente en `/checkout/*`.
- **`next/font`** (autohospedada, `display: swap`) → sin CLS por fuentes ni requests a Google en runtime.
- **Skeletons con dimensiones fijas** (`aspect-ratio`) → CLS ≈ 0 al llegar los datos.
- **Cache con tags** en lecturas públicas + invalidación precisa tras mutaciones.

## 4. Si el score de Performance es bajo, revisa

- Imágenes: si tu API devuelve `image_url`, migra de `<img>` a `next/image` y declara el dominio en `images.remotePatterns` (mejora LCP).
- Latencia de la API local (`php artisan serve` es monohilo): mide también con `--throttling-method=provided`.
