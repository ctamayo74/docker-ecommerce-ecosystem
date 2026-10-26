import { notFound, redirect } from "next/navigation";
import type { Metadata } from "next";
import { getOrder } from "@/lib/data";
import { OrderLines } from "@/components/order-summary";
import { PaymentForm } from "@/components/payment-form";

export const metadata: Metadata = { title: "Pago" };

export default async function CheckoutPage({ params }: { params: Promise<{ orderId: string }> }) {
  const id = Number((await params).orderId);
  if (!Number.isInteger(id)) notFound();

  const order = await getOrder(id);
  if (!order) notFound();
  if (order.status === "paid") redirect(`/orders/${id}`);

  return (
    <div className="mx-auto grid max-w-4xl gap-6 md:grid-cols-2">
      <section className="card p-5">
        <h1 className="mb-3 text-xl font-bold">Orden #{order.id}</h1>
        <OrderLines order={order} />
      </section>
      <section className="card p-5">
        <h2 className="mb-3 text-xl font-bold">Pago con tarjeta</h2>
        <PaymentForm orderId={order.id} total={order.total} />
      </section>
    </div>
  );
}
