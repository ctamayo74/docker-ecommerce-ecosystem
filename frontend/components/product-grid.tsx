import Link from "next/link";
import { getProducts } from "@/lib/data";
import { ProductCard } from "./product-card";

/** Componente async: hace el fetch en el servidor y se transmite vía <Suspense>. */
export async function ProductGrid({ search, page }: { search?: string; page: number }) {
  const { items, currentPage, lastPage } = await getProducts({ search, page });

  if (!items.length) {
    return (
      <div className="card p-10 text-center">
        <p className="font-semibold">No encontramos productos{search ? ` para “${search}”` : ""}.</p>
        {search && <Link href="/products" className="mt-3 inline-block text-brand underline">Ver todo el catálogo</Link>}
      </div>
    );
  }

  const href = (p: number) => {
    const qs = new URLSearchParams();
    if (search) qs.set("search", search);
    if (p > 1) qs.set("page", String(p));
    const q = qs.toString();
    return `/products${q ? `?${q}` : ""}`;
  };

  return (
    <>
      <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        {items.map((p) => <ProductCard key={p.id} product={p} />)}
      </ul>
      {lastPage > 1 && (
        <nav className="mt-8 flex items-center justify-center gap-4" aria-label="Paginación">
          {currentPage > 1 && <Link className="btn btn-ghost" href={href(currentPage - 1)}>Anterior</Link>}
          <span className="text-sm text-ink/60">Página {currentPage} de {lastPage}</span>
          {currentPage < lastPage && <Link className="btn btn-ghost" href={href(currentPage + 1)}>Siguiente</Link>}
        </nav>
      )}
    </>
  );
}

export function ProductGridSkeleton() {
  return (
    <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-busy="true" aria-label="Cargando productos">
      {Array.from({ length: 8 }).map((_, i) => (
        <li key={i} className="card overflow-hidden">
          <div className="aspect-[4/3] animate-pulse bg-line" />
          <div className="space-y-2 p-4">
            <div className="h-4 w-2/3 animate-pulse rounded bg-line" />
            <div className="h-3 w-full animate-pulse rounded bg-line" />
            <div className="h-5 w-1/3 animate-pulse rounded bg-line" />
          </div>
        </li>
      ))}
    </ul>
  );
}
