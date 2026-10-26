"use client";

import { useState } from "react";
import { useCart } from "./cart-provider";
import type { Product } from "@/lib/types";

export function AddToCartButton({ product }: { product: Product }) {
  const { items, add } = useCart();
  const [added, setAdded] = useState(false);
  const inCart = items.find((i) => i.productId === product.id)?.quantity ?? 0;
  const maxed = inCart >= product.stock;

  if (product.stock <= 0) {
    return <button className="btn btn-primary" disabled>Agotado</button>;
  }

  return (
    <div>
      <button
        className="btn btn-primary"
        disabled={maxed}
        onClick={() => {
          add({ productId: product.id, name: product.name, price: product.price, stock: product.stock });
          setAdded(true);
          setTimeout(() => setAdded(false), 1500);
        }}
      >
        {maxed ? "Máximo en el carrito" : "Agregar al carrito"}
      </button>
      <p className="mt-2 h-5 text-sm text-brand" role="status" aria-live="polite">
        {added ? "Agregado al carrito" : inCart > 0 ? `${inCart} en tu carrito` : ""}
      </p>
    </div>
  );
}
