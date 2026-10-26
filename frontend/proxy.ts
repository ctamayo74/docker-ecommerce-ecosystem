import { NextResponse, type NextRequest } from "next/server";
import { TOKEN_COOKIE } from "@/lib/config";

/**
 * Next.js 16: `proxy.ts` reemplaza a `middleware.ts`.
 * Primera barrera (rápida) para rutas protegidas: exige la cookie de sesión.
 * La validez real del token la verifica la API en cada lectura (ver lib/data.ts).
 */
export function proxy(req: NextRequest) {
  if (!req.cookies.has(TOKEN_COOKIE)) {
    const url = new URL("/login", req.url);
    url.searchParams.set("next", req.nextUrl.pathname + req.nextUrl.search);
    return NextResponse.redirect(url);
  }
  return NextResponse.next();
}

export const config = { matcher: ["/orders/:path*", "/checkout/:path*"] };
