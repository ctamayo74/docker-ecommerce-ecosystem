import Link from "next/link";

export default function NotFound() {
  return (
    <div className="card mx-auto max-w-lg p-8 text-center">
      <h1 className="text-xl font-bold">No encontramos lo que buscas</h1>
      <p className="mt-2 text-ink/70">El recurso no existe o ya no está disponible.</p>
      <Link href="/products" className="btn btn-primary mt-5">Volver al catálogo</Link>
    </div>
  );
}
