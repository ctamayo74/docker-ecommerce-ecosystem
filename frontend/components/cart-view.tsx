"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import { createOrder } from "@/app/actions/orders";
import { formatMoney } from "@/lib/format";
import { useCart } from "./cart-provider";

export function CartView() {
  const { items, ready, total, setQty, remove, clear } = useCart();
  const router = useRouter();
  const [pending, startTransition] = useTransition();
  const [error, setError] = useState<string | null>(null);

  if (!ready) return <div className="h-40 animate-pulse rounded-lg bg-line" aria-busy="true" />;

  if (!items.length) {
    return (
      <div className="card p-10 text-center">
        <p className="font-semibold">Tu carrito está vacío.</p>
        <Link href="/products" className="btn btn-primary mt-4">Explorar el catálogo</Link>
      </div>
    );
  }

  const checkout = () => {
    setError(null);
    startTransition(async () => {
      const res = await createOrder(items.map((i) => ({ productId: i.productId, quantity: i.quantity })));
      if (res.error || !res.orderId) return setError(res.error ?? "No se pudo crear la orden.");
      clear(); // la orden ya existe en la API; el carrito local se vacía
      router.push(`/checkout/${res.orderId}`);
    });
  };

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
      <ul className="card divide-y divide-line">
        {items.map((i) => (
          <li key={i.productId} className="flex flex-wrap items-center gap-4 p-4">
            <div className="min-w-0 flex-1">
              <Link href={`/products/${i.productId}`} className="font-semibold hover:text-brand">{i.name}</Link>
              <p className="text-sm text-ink/60">{formatMoney(i.price)} c/u</p>
            </div>
            <div className="flex items-center gap-2">
              <button className="btn btn-ghost px-3!" onClick={() => setQty(i.productId, i.quantity - 1)} aria-label={`Quitar una unidad de ${i.name}`}>−</button>
              <span className="w-8 text-center" aria-live="polite">{i.quantity}</span>
              <button className="btn btn-ghost px-3!" onClick={() => setQty(i.productId, i.quantity + 1)} disabled={i.quantity >= i.stock} aria-label={`Agregar una unidad de ${i.name}`}>+</button>
            </div>
            <p className="w-24 text-right font-semibold">{formatMoney(i.price * i.quantity)}</p>
            <button className="text-sm text-red-700 underline" onClick={() => remove(i.productId)}>Quitar</button>
          </li>
        ))}
      </ul>
      <aside className="card h-fit p-5">
        <h2 className="font-bold">Resumen</h2>
        <p className="mt-3 flex justify-between text-lg"><span>Total</span><strong>{formatMoney(total)}</strong></p>
        {error && <p className="mt-3 rounded bg-red-50 p-3 text-sm text-red-800" role="alert">{error}</p>}
        <button className="btn btn-primary mt-4 w-full" onClick={checkout} disabled={pending}>
          {pending ? "Creando orden…" : "Crear orden y pagar"}
        </button>
        <p className="mt-2 text-xs text-ink/60">Al crear la orden se reserva el stock. El pago se realiza en el siguiente paso.</p>
      </aside>
    </div>
  );
}
