"use server";

import { redirect } from "next/navigation";
import { revalidatePath, updateTag } from "next/cache";
import { api, ApiError } from "@/lib/api";

/* eslint-disable @typescript-eslint/no-explicit-any */

type CartLine = { productId: number; quantity: number };

export async function createOrder(items: CartLine[]): Promise<{ orderId?: number; error?: string }> {
  if (!items.length) return { error: "Tu carrito está vacío." };

  let orderId: number | undefined;
  let unauthorized = false;
  try {
    const json: any = await api("/orders", {
      method: "POST",
      auth: true,
      body: { items: items.map((i) => ({ product_id: i.productId, quantity: i.quantity })) },
    });
    orderId = Number(json?.data?.id ?? json?.id);
  } catch (e) {
    if (e instanceof ApiError && e.status === 401) unauthorized = true;
    else return { error: e instanceof ApiError ? e.message : "No se pudo crear la orden." };
  }
  if (unauthorized) redirect("/login?next=/cart");
  if (!orderId) return { error: "La API no devolvió el id de la orden." };

  // La API descontó stock y creó la orden: invalidar para que nada quede desactualizado.
  updateTag("products");
  revalidatePath("/orders");
  return { orderId };
}

export async function payOrder(orderId: number, paymentMethodId: string): Promise<{ error: string }> {
  let unauthorized = false;
  let alreadyPaid = false;
  try {
    await api(`/orders/${orderId}/pay`, {
      method: "POST",
      auth: true,
      body: { payment_method_id: paymentMethodId },
    });
  } catch (e) {
    if (e instanceof ApiError && e.status === 401) unauthorized = true;
    else if (e instanceof ApiError && e.status === 409) alreadyPaid = true;
    else if (e instanceof ApiError && e.status === 402) {
      revalidatePath("/orders"); // la orden pudo quedar en "failed"
      revalidatePath(`/orders/${orderId}`);
      return { error: `Pago rechazado: ${e.message}` };
    } else return { error: e instanceof ApiError ? e.message : "No se pudo procesar el pago." };
  }
  if (unauthorized) redirect(`/login?next=/checkout/${orderId}`);
  if (alreadyPaid) redirect(`/orders/${orderId}`);

  revalidatePath("/orders");
  revalidatePath(`/orders/${orderId}`);
  redirect(`/orders/${orderId}?paid=1`);
}
