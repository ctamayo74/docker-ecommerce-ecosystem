import { Suspense } from "react";
import type { Metadata } from "next";
import { OrderList, OrderListSkeleton } from "@/components/order-list";

export const metadata: Metadata = { title: "Mis compras" };

export default function OrdersPage() {
  return (
    <>
      <h1 className="mb-6 text-3xl font-bold tracking-tight">Mis compras</h1>
      <Suspense fallback={<OrderListSkeleton />}>
        <OrderList />
      </Suspense>
    </>
  );
}
