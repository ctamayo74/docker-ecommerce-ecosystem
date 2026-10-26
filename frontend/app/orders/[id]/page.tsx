import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { getOrder } from "@/lib/data";
import { formatDate, STATUS_LABEL } from "@/lib/format";
import { OrderLines } from "@/components/order-summary";

export const metadata: Metadata = { title: "Detalle de compra" };

export default async function OrderPage({
  params,
  searchParams,
}: {
  params: Promise<{ id: string }>;
  searchParams: Promise<{ paid?: string }>;
}) {
  const id = Number((await params).id);
  if (!Number.isInteger(id)) notFound();
  const order = await getOrder(id);
  if (!order) notFound();

  const { paid } = await searchParams;
  // La confirmación se basa en el estado real devuelto por la API, no solo en el query string.
  const confirmed = paid === "1" && order.status === "paid";

  return (
    <div className="mx-auto max-w-2xl">
      {confirmed && (
        <div className="mb-6 rounded-lg border border-emerald-300 bg-emerald-50 p-5" role="status">
          <h1 className="text-2xl font-bold text-emerald-900">¡Compra confirmada!</h1>
          <p className="mt-1 text-emerald-900/80">Recibimos tu pago de la orden #{order.id}.</p>
        </div>
      )}
      <section className="card p-5">
        <div className="mb-3 flex items-baseline justify-between">
          <h2 className="text-xl font-bold">Orden #{order.id}</h2>
          <span className="text-sm text-ink/60">{STATUS_LABEL[order.status] ?? order.status} · {formatDate(order.createdAt)}</span>
        </div>
        <OrderLines order={order} />
        {order.status !== "paid" && (
          <Link href={`/checkout/${order.id}`} className="btn btn-primary mt-4">Completar pago</Link>
        )}
      </section>
      <Link href="/orders" className="mt-4 inline-block text-sm text-brand underline">Ver todas mis compras</Link>
    </div>
  );
}
