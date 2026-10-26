"use client";

import { createContext, useContext, useEffect, useMemo, useReducer, useState } from "react";

export type CartItem = { productId: number; name: string; price: number; stock: number; quantity: number };

type Action =
  | { type: "hydrate"; items: CartItem[] }
  | { type: "add"; item: Omit<CartItem, "quantity"> }
  | { type: "setQty"; productId: number; quantity: number }
  | { type: "remove"; productId: number }
  | { type: "clear" };

function reducer(state: CartItem[], a: Action): CartItem[] {
  switch (a.type) {
    case "hydrate":
      return a.items;
    case "add": {
      const found = state.find((i) => i.productId === a.item.productId);
      if (!found) return a.item.stock > 0 ? [...state, { ...a.item, quantity: 1 }] : state;
      return state.map((i) =>
        i.productId === a.item.productId
          ? { ...i, stock: a.item.stock, quantity: Math.min(i.quantity + 1, a.item.stock) }
          : i,
      );
    }
    case "setQty":
      return state
        .map((i) => (i.productId === a.productId ? { ...i, quantity: Math.min(a.quantity, i.stock) } : i))
        .filter((i) => i.quantity > 0);
    case "remove":
      return state.filter((i) => i.productId !== a.productId);
    case "clear":
      return [];
  }
}

type Ctx = {
  items: CartItem[];
  ready: boolean;
  count: number;
  total: number;
  add: (item: Omit<CartItem, "quantity">) => void;
  setQty: (productId: number, quantity: number) => void;
  remove: (productId: number) => void;
  clear: () => void;
};

const CartContext = createContext<Ctx | null>(null);
const KEY = "ecom-cart-v1";

export function CartProvider({ children }: { children: React.ReactNode }) {
  const [items, dispatch] = useReducer(reducer, []);
  const [ready, setReady] = useState(false);

  // Hidratar tras el montaje (evita mismatch SSR/cliente)
  useEffect(() => {
    try {
      const raw = localStorage.getItem(KEY);
      if (raw) dispatch({ type: "hydrate", items: JSON.parse(raw) });
    } catch {}
    setReady(true);
  }, []);

  useEffect(() => {
    if (ready) localStorage.setItem(KEY, JSON.stringify(items));
  }, [items, ready]);

  const value = useMemo<Ctx>(
    () => ({
      items,
      ready,
      count: items.reduce((n, i) => n + i.quantity, 0),
      total: items.reduce((n, i) => n + i.quantity * i.price, 0),
      add: (item) => dispatch({ type: "add", item }),
      setQty: (productId, quantity) => dispatch({ type: "setQty", productId, quantity }),
      remove: (productId) => dispatch({ type: "remove", productId }),
      clear: () => dispatch({ type: "clear" }),
    }),
    [items, ready],
  );

  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error("useCart debe usarse dentro de <CartProvider>");
  return ctx;
}
