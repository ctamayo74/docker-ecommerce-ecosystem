"use client";

import Link from "next/link";
import { useCart } from "./cart-provider";

export function CartLink() {
  const { count, ready } = useCart();
  return (
    <Link href="/cart" className="nav-link">
      Carrito
      <span className="ml-2 inline-flex min-w-6 justify-center rounded-full bg-ink px-1.5 text-xs text-white" aria-label={`${count} productos`}>
        {ready ? count : 0}
      </span>
    </Link>
  );
}
