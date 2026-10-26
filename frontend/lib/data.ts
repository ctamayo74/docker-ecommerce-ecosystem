import "server-only";
import { redirect } from "next/navigation";
import { api, ApiError } from "./api";
import type { Order, Page, Product } from "./types";

/* eslint-disable @typescript-eslint/no-explicit-any */
type Raw = Record<string, any>;

/* ---------- Normalizadores: aíslan al UI de variaciones en la forma de la API ---------- */

const toProduct = (p: Raw): Product => ({
  id: Number(p.id),
  name: p.name ?? "Producto",
  description: p.description ?? "",
  price: Number(p.price ?? 0),
  stock: Number(p.stock ?? 0),
  imageUrl: p.image_url ?? p.image ?? null,
});

const toOrder = (o: Raw): Order => ({
  id: Number(o.id),
  status: o.status ?? "pending",
  total: Number(o.total ?? o.total_amount ?? 0),
  createdAt: o.created_at ?? null,
  items: (o.items ?? o.order_items ?? []).map((i: Raw) => ({
    productId: Number(i.product_id ?? i.product?.id),
    name: i.product?.name ?? i.product_name ?? i.name ?? `Producto #${i.product_id}`,
    quantity: Number(i.quantity ?? 0),
    unitPrice: Number(i.unit_price ?? i.price ?? i.product?.price ?? 0),
  })),
});

/** Soporta `{data: [...], meta}` (Resource paginado) y `{data: {data: [...], ...}}`. */
function toPage<T>(json: Raw, map: (r: Raw) => T): Page<T> {
  const list: Raw[] = Array.isArray(json.data) ? json.data : (json.data?.data ?? []);
  const meta: Raw = json.meta ?? json.data?.meta ?? json.data ?? json;
  return {
    items: list.map(map),
    currentPage: Number(meta.current_page ?? 1),
    lastPage: Number(meta.last_page ?? 1),
  };
}

const unwrap = (json: Raw): Raw => json.data ?? json;

/** Si el token expiró o es inválido, manda al login (fuera de try/catch). */
async function authed<T>(fn: () => Promise<T>): Promise<T> {
  let unauthorized = false;
  let result: T | undefined;
  try {
    result = await fn();
  } catch (e) {
    if (e instanceof ApiError && e.status === 401) unauthorized = true;
    else throw e;
  }
  if (unauthorized) redirect("/login?expired=1");
  return result as T;
}

/* ---------- Lecturas públicas (Server Components, cache con tags) ---------- */

export async function getProducts(params: { search?: string; page?: number }): Promise<Page<Product>> {
  const qs = new URLSearchParams();
  if (params.search) qs.set("search", params.search);
  if (params.page && params.page > 1) qs.set("page", String(params.page));
  const q = qs.toString();
  const suffix = q ? `?${q}` : "";
  return toPage(await api<Raw>(`/products${suffix}`, { tags: ["products"] }), toProduct);
}

export async function getProduct(id: number): Promise<Product | null> {
  try {
    return toProduct(unwrap(await api<Raw>(`/products/${id}`, { tags: ["products", `product-${id}`] })));
  } catch (e) {
    if (e instanceof ApiError && e.status === 404) return null;
    throw e;
  }
}

/* ---------- Lecturas protegidas (dinámicas, token por request) ---------- */

export const getMe = () =>
  api<Raw>("/auth/me", { auth: true }).then((j) => unwrap(j) as { name?: string; email?: string });

export const getOrders = () =>
  authed(async () => toPage(await api<Raw>("/orders", { auth: true }), toOrder));

export const getOrder = (id: number) =>
  authed(async () => {
    try {
      return toOrder(unwrap(await api<Raw>(`/orders/${id}`, { auth: true })));
    } catch (e) {
      if (e instanceof ApiError && (e.status === 404 || e.status === 403)) return null;
      throw e;
    }
  });
