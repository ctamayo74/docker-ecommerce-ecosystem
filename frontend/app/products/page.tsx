import { Suspense } from "react";
import type { Metadata } from "next";
import { ProductGrid, ProductGridSkeleton } from "@/components/product-grid";

export const metadata: Metadata = { title: "Catálogo" };

export default async function ProductsPage({
  searchParams,
}: {
  searchParams: Promise<{ search?: string; page?: string }>;
}) {
  const { search, page } = await searchParams;
  const pageNum = Math.max(1, Number(page) || 1);

  return (
    <>
      <div className="mb-6 flex flex-wrap items-end justify-between gap-4">
        <h1 className="text-3xl font-bold tracking-tight">Catálogo</h1>
        <form action="/products" role="search" className="flex gap-2">
          <label htmlFor="search" className="sr-only">Buscar productos</label>
          <input id="search" name="search" defaultValue={search} placeholder="Buscar por nombre" className="field w-64" />
          <button className="btn btn-ghost">Buscar</button>
        </form>
      </div>
      {/* El shell se envía de inmediato; la lista llega por streaming */}
      <Suspense key={`${search}-${pageNum}`} fallback={<ProductGridSkeleton />}>
        <ProductGrid search={search} page={pageNum} />
      </Suspense>
    </>
  );
}
