import Link from "next/link";
import { formatMoney } from "@/lib/format";
import type { Product } from "@/lib/types";

const TINTS = ["#d9ebe6", "#f4e6c4", "#dfe6f2", "#efdcd5", "#e3e8d3"];

export function ProductThumb({ product, className = "" }: { product: Product; className?: string }) {
  return product.imageUrl ? (
    // eslint-disable-next-line @next/next/no-img-element
    <img src={product.imageUrl} alt={product.name} loading="lazy" className={`object-cover ${className}`} />
  ) : (
    <div
      className={`flex items-center justify-center text-5xl font-bold text-ink/40 ${className}`}
      style={{ background: TINTS[product.id % TINTS.length] }}
      aria-hidden
    >
      {product.name.charAt(0).toUpperCase()}
    </div>
  );
}

export function ProductCard({ product }: { product: Product }) {
  const soldOut = product.stock <= 0;
  return (
    <li>
      <Link href={`/products/${product.id}`} className="card block overflow-hidden transition-colors hover:border-brand">
        <ProductThumb product={product} className="aspect-[4/3] w-full" />
        <div className="p-4">
          <h3 className="font-semibold">{product.name}</h3>
          <p className="mt-1 line-clamp-2 text-sm text-ink/60">{product.description}</p>
          <div className="mt-3 flex items-baseline justify-between">
            <span className="text-lg font-bold">{formatMoney(product.price)}</span>
            <span className={`text-sm ${soldOut ? "text-red-700" : "text-ink/60"}`}>
              {soldOut ? "Agotado" : `${product.stock} disponibles`}
            </span>
          </div>
        </div>
      </Link>
    </li>
  );
}
