"use client";

export default function GlobalSegmentError({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return (
    <div className="card mx-auto max-w-lg p-8 text-center" role="alert">
      <h1 className="text-xl font-bold">Algo salió mal</h1>
      <p className="mt-2 text-ink/70">{error.message || "No pudimos cargar esta página."}</p>
      {error.digest && <p className="mt-1 text-xs text-ink/50">Código: {error.digest}</p>}
      <button onClick={reset} className="btn btn-primary mt-5">Reintentar</button>
    </div>
  );
}
