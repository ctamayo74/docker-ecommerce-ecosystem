import { formatMoney } from "@/lib/format";
import type { Order } from "@/lib/types";

export function OrderLines({ order }: { order: Order }) {
  return (
    <>
      <ul className="divide-y divide-line">
        {order.items.map((i, idx) => (
          <li key={`${i.productId}-${idx}`} className="flex justify-between gap-4 py-2 text-sm">
            <span>{i.quantity} × {i.name}</span>
            <span>{formatMoney(i.quantity * i.unitPrice)}</span>
          </li>
        ))}
      </ul>
      <p className="mt-3 flex justify-between border-t border-line pt-3 font-bold"><span>Total</span><span>{formatMoney(order.total)}</span></p>
    </>
  );
}
