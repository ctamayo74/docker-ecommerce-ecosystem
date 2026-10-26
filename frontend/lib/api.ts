import "server-only";
import { cookies } from "next/headers";
import { API_URL, TOKEN_COOKIE } from "./config";

export class ApiError extends Error {
  constructor(
    public status: number,
    message: string,
    public errors?: Record<string, string[]>,
  ) {
    super(message);
  }
}

type Options = {
  method?: "GET" | "POST" | "PUT" | "PATCH" | "DELETE";
  body?: unknown;
  /** Adjunta el Bearer token leído de la cookie httpOnly. */
  auth?: boolean;
  /** Tags para revalidación (solo lecturas públicas). */
  tags?: string[];
  revalidate?: number;
};

/**
 * Único punto de salida hacia la API Laravel. Solo corre en el servidor:
 * el token vive en una cookie httpOnly y nunca se expone al JS del navegador.
 */
export async function api<T = unknown>(path: string, opts: Options = {}): Promise<T> {
  const { method = "GET", body, auth = false, tags, revalidate = 60 } = opts;
  const headers: Record<string, string> = { Accept: "application/json" };
  if (body !== undefined) headers["Content-Type"] = "application/json";

  if (auth) {
    const token = (await cookies()).get(TOKEN_COOKIE)?.value;
    if (!token) throw new ApiError(401, "No autenticado");
    headers.Authorization = `Bearer ${token}`;
  }

  // Lecturas públicas: cacheadas con tags. Todo lo demás (auth / mutaciones): sin caché.
  const cacheable = method === "GET" && !auth;
  const init: RequestInit = {
    method,
    headers,
    body: body !== undefined ? JSON.stringify(body) : undefined,
    ...(cacheable ? { next: { tags, revalidate } } : { cache: "no-store" as const }),
  };

  let res: Response;
  try {
    res = await fetch(`${API_URL}${path}`, init);
  } catch {
    throw new ApiError(503, "No se pudo conectar con el servidor. Inténtalo de nuevo en unos minutos.");
  }

  const json = await res.json().catch(() => null);
  if (!res.ok) {
    throw new ApiError(res.status, json?.message ?? `Error inesperado (${res.status})`, json?.errors);
  }
  return json as T;
}
