import { ProductGridSkeleton } from "@/components/product-grid";

export default function Loading() {
  return (
    <>
      <div className="mb-6 h-9 w-48 animate-pulse rounded bg-line" />
      <ProductGridSkeleton />
    </>
  );
}
