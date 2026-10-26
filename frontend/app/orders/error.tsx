"use client";

export default function OrdersError({ error, reset }: { error: Error; reset: () => void }) {
  return (
    <div className="card mx-auto max-w-lg p-8 text-center" role="alert">
      <h1 className="text-xl font-bold">No pudimos cargar tus compras</h1>
      <p className="mt-2 text-ink/70">{error.message}</p>
      <button onClick={reset} className="btn btn-primary mt-5">Reintentar</button>
    </div>
  );
}
