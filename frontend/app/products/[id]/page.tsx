import Link from "next/link";
import { notFound } from "next/navigation";
import type { Metadata } from "next";
import { getProduct } from "@/lib/data";
import { formatMoney } from "@/lib/format";
import { AddToCartButton } from "@/components/add-to-cart-button";
import { ProductThumb } from "@/components/product-card";

type Props = { params: Promise<{ id: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const product = await getProduct(Number((await params).id));
  return { title: product?.name ?? "Producto" };
}

export default async function ProductPage({ params }: Props) {
  const id = Number((await params).id);
  if (!Number.isInteger(id)) notFound();
  const product = await getProduct(id);
  if (!product) notFound();

  return (
    <article>
      <Link href="/products" className="text-sm text-brand underline">Volver al catálogo</Link>
      <div className="mt-4 grid gap-8 md:grid-cols-2">
        <ProductThumb product={product} className="aspect-square w-full rounded-lg" />
        <div>
          <h1 className="text-3xl font-bold tracking-tight">{product.name}</h1>
          <p className="mt-3 text-3xl font-bold text-brand">{formatMoney(product.price)}</p>
          <p className="mt-4 max-w-prose text-ink/80">{product.description || "Sin descripción."}</p>
          <p className="mt-4 text-sm text-ink/60">
            {product.stock > 0 ? `${product.stock} unidades disponibles` : "Sin stock por ahora"}
          </p>
          <div className="mt-6"><AddToCartButton product={product} /></div>
        </div>
      </div>
    </article>
  );
}
