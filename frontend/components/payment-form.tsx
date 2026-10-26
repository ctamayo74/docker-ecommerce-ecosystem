"use client";

import { useState, useTransition } from "react";
import { loadStripe } from "@stripe/stripe-js";
import { CardElement, Elements, useElements, useStripe } from "@stripe/react-stripe-js";
import { payOrder } from "@/app/actions/orders";
import { formatMoney } from "@/lib/format";

const key = process.env.NEXT_PUBLIC_STRIPE_KEY;
const stripePromise = key ? loadStripe(key) : null;

function CheckoutForm({ orderId, total }: { orderId: number; total: number }) {
  const stripe = useStripe();
  const elements = useElements();
  const [error, setError] = useState<string | null>(null);
  const [pending, startTransition] = useTransition();

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    const card = elements?.getElement(CardElement);
    if (!stripe || !card) return;

    // Stripe.js tokeniza la tarjeta: el número NUNCA toca nuestro servidor ni la API.
    const { paymentMethod, error: stripeError } = await stripe.createPaymentMethod({ type: "card", card });
    if (stripeError || !paymentMethod) return setError(stripeError?.message ?? "Tarjeta inválida.");

    startTransition(async () => {
      const res = await payOrder(orderId, paymentMethod.id); // Server Action; en éxito redirige
      if (res?.error) setError(res.error);
    });
  };

  return (
    <form onSubmit={onSubmit} className="space-y-4">
      <div className="field py-3!"><CardElement options={{ hidePostalCode: true }} /></div>
      {error && <p className="rounded bg-red-50 p-3 text-sm text-red-800" role="alert">{error}</p>}
      <button className="btn btn-primary w-full" disabled={!stripe || pending}>
        {pending ? "Procesando pago…" : `Pagar ${formatMoney(total)}`}
      </button>
      <p className="text-xs text-ink/60">Modo prueba: usa la tarjeta 4242 4242 4242 4242, cualquier fecha futura y CVC.</p>
    </form>
  );
}

export function PaymentForm(props: { orderId: number; total: number }) {
  if (!stripePromise) {
    return <p className="rounded bg-amber-50 p-3 text-sm text-amber-900">Falta configurar NEXT_PUBLIC_STRIPE_KEY en .env.local.</p>;
  }
  return (
    <Elements stripe={stripePromise}>
      <CheckoutForm {...props} />
    </Elements>
  );
}
