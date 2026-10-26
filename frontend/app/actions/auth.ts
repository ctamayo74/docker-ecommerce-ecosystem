"use server";

import { cookies } from "next/headers";
import { redirect } from "next/navigation";
import { revalidatePath } from "next/cache";
import { api, ApiError } from "@/lib/api";
import { TOKEN_COOKIE } from "@/lib/config";
import type { FormState } from "@/lib/types";

/* eslint-disable @typescript-eslint/no-explicit-any */

function extractToken(json: any): string {
  const token = json?.token ?? json?.data?.token ?? json?.data?.access_token ?? json?.access_token;
  if (!token) throw new ApiError(500, "La API no devolvió un token de sesión.");
  return String(token);
}

async function saveToken(token: string) {
  (await cookies()).set(TOKEN_COOKIE, token, {
    httpOnly: true, // inaccesible desde JS → mitiga robo por XSS
    secure: process.env.NODE_ENV === "production",
    sameSite: "lax", // mitiga CSRF en navegación cross-site
    path: "/",
    maxAge: 60 * 60 * 24 * 7,
  });
}

/** Evita open-redirect: solo rutas internas. */
const safeNext = (v: FormDataEntryValue | null) => {
  const s = typeof v === "string" ? v : "";
  return s.startsWith("/") && !s.startsWith("//") ? s : "/products";
};

const toState = (e: unknown): FormState =>
  e instanceof ApiError
    ? { error: e.message, fieldErrors: e.errors }
    : { error: "Ocurrió un error inesperado. Inténtalo de nuevo." };

export async function login(_prev: FormState, formData: FormData): Promise<FormState> {
  try {
    const json = await api("/auth/login", {
      method: "POST",
      body: { email: String(formData.get("email") ?? ""), password: String(formData.get("password") ?? "") },
    });
    await saveToken(extractToken(json));
  } catch (e) {
    return toState(e);
  }
  revalidatePath("/", "layout");
  redirect(safeNext(formData.get("next")));
}

export async function register(_prev: FormState, formData: FormData): Promise<FormState> {
  try {
    const json = await api("/auth/register", {
      method: "POST",
      body: {
        name: String(formData.get("name") ?? ""),
        email: String(formData.get("email") ?? ""),
        password: String(formData.get("password") ?? ""),
        password_confirmation: String(formData.get("password_confirmation") ?? ""),
      },
    });
    await saveToken(extractToken(json));
  } catch (e) {
    return toState(e);
  }
  revalidatePath("/", "layout");
  redirect(safeNext(formData.get("next")));
}

export async function logout() {
  try {
    await api("/auth/logout", { method: "POST", auth: true }); // revoca el token en la API
  } catch {
    /* si ya expiró, igualmente cerramos sesión local */
  }
  (await cookies()).delete(TOKEN_COOKIE);
  revalidatePath("/", "layout");
  redirect("/products");
}
