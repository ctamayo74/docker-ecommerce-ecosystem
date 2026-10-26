import { OrderListSkeleton } from "@/components/order-list";

export default function Loading() {
  return (
    <>
      <div className="mb-6 h-9 w-48 animate-pulse rounded bg-line" />
      <OrderListSkeleton />
    </>
  );
}
