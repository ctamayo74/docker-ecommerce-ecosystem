import Link from "next/link";
import { Suspense } from "react";
import { cookies } from "next/headers";
import { TOKEN_COOKIE } from "@/lib/config";
import { getMe } from "@/lib/data";
import { logout } from "@/app/actions/auth";
import { CartLink } from "./cart-link";

async function UserMenu() {
  const token = (await cookies()).get(TOKEN_COOKIE)?.value;
  if (!token) {
    return (
      <>
        <Link href="/login" className="nav-link">Ingresar</Link>
        <Link href="/register" className="btn btn-primary py-1.5!">Crear cuenta</Link>
      </>
    );
  }
  const me = await getMe().catch(() => null);
  return (
    <>
      <Link href="/orders" className="nav-link">Mis compras</Link>
      <span className="hidden text-sm text-ink/60 sm:inline">{me?.name ?? me?.email}</span>
      <form action={logout}>
        <button className="nav-link">Salir</button>
      </form>
    </>
  );
}

export function SiteHeader() {
  return (
    <header className="border-b border-line bg-white">
      <div className="mx-auto flex max-w-6xl items-center gap-4 px-4 py-3">
        <Link href="/products" className="text-xl font-bold tracking-tight">Tienda</Link>
        <nav className="ml-auto flex items-center gap-4" aria-label="Principal">
          <Link href="/products" className="nav-link">Catálogo</Link>
          <CartLink />
          <Suspense fallback={<span className="h-5 w-24 animate-pulse rounded bg-line" />}>
            <UserMenu />
          </Suspense>
        </nav>
      </div>
    </header>
  );
}
