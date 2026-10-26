import Link from "next/link";
import { getOrders } from "@/lib/data";
import { formatDate, formatMoney, STATUS_LABEL } from "@/lib/format";

const BADGE: Record<string, string> = {
  paid: "bg-emerald-100 text-emerald-900",
  pending: "bg-amber-100 text-amber-900",
  failed: "bg-red-100 text-red-900",
};

export async function OrderList() {
  const { items: orders } = await getOrders();

  if (!orders.length) {
    return (
      <div className="card p-10 text-center">
        <p className="font-semibold">Aún no tienes compras.</p>
        <Link href="/products" className="btn btn-primary mt-4">Ir al catálogo</Link>
      </div>
    );
  }

  return (
    <ul className="card divide-y divide-line">
      {orders.map((o) => (
        <li key={o.id}>
          <Link href={o.status === "paid" ? `/orders/${o.id}` : `/checkout/${o.id}`} className="flex flex-wrap items-center gap-4 p-4 hover:bg-paper">
            <div className="flex-1">
              <p className="font-semibold">Orden #{o.id}</p>
              <p className="text-sm text-ink/60">{formatDate(o.createdAt)} · {o.items.length} {o.items.length === 1 ? "producto" : "productos"}</p>
            </div>
            <span className={`rounded-full px-3 py-1 text-xs font-semibold ${BADGE[o.status] ?? "bg-line"}`}>{STATUS_LABEL[o.status] ?? o.status}</span>
            <span className="w-24 text-right font-bold">{formatMoney(o.total)}</span>
          </Link>
        </li>
      ))}
    </ul>
  );
}

export function OrderListSkeleton() {
  return (
    <div className="card divide-y divide-line" aria-busy="true" aria-label="Cargando compras">
      {Array.from({ length: 4 }).map((_, i) => (
        <div key={i} className="flex gap-4 p-4">
          <div className="h-10 flex-1 animate-pulse rounded bg-line" />
          <div className="h-10 w-24 animate-pulse rounded bg-line" />
        </div>
      ))}
    </div>
  );
}
