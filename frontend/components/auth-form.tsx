"use client";

import Link from "next/link";
import { useActionState } from "react";
import { login, register } from "@/app/actions/auth";

function Field({
  name, label, type = "text", auto, error,
}: { name: string; label: string; type?: string; auto?: string; error?: string }) {
  return (
    <div>
      <label htmlFor={name} className="mb-1 block text-sm font-medium">{label}</label>
      <input
        id={name} name={name} type={type} required autoComplete={auto} className="field"
        aria-invalid={!!error} aria-describedby={error ? `${name}-err` : undefined}
      />
      {error && <p id={`${name}-err`} className="mt-1 text-sm text-red-700">{error}</p>}
    </div>
  );
}

export function AuthForm({ mode, next, expired }: { mode: "login" | "register"; next?: string; expired?: boolean }) {
  const [state, action, pending] = useActionState(mode === "login" ? login : register, undefined);
  const isLogin = mode === "login";
  const err = (f: string) => state?.fieldErrors?.[f]?.[0];

  return (
    <div className="card mx-auto max-w-md p-6">
      <h1 className="text-2xl font-bold">{isLogin ? "Ingresar" : "Crear cuenta"}</h1>
      {expired && <p className="mt-3 rounded bg-amber-50 p-3 text-sm text-amber-900" role="status">Tu sesión expiró. Ingresa de nuevo.</p>}
      <form action={action} className="mt-5 space-y-4">
        <input type="hidden" name="next" value={next ?? ""} />
        {!isLogin && <Field name="name" label="Nombre" auto="name" error={err("name")} />}
        <Field name="email" label="Correo electrónico" type="email" auto="email" error={err("email")} />
        <Field name="password" label="Contraseña" type="password" auto={isLogin ? "current-password" : "new-password"} error={err("password")} />
        {!isLogin && <Field name="password_confirmation" label="Confirmar contraseña" type="password" auto="new-password" error={err("password_confirmation")} />}
        {state?.error && <p className="rounded bg-red-50 p-3 text-sm text-red-800" role="alert">{state.error}</p>}
        <button className="btn btn-primary w-full" disabled={pending}>
          {pending ? "Procesando…" : isLogin ? "Ingresar" : "Crear cuenta"}
        </button>
      </form>
      <p className="mt-4 text-sm text-ink/70">
        {isLogin ? "¿No tienes cuenta? " : "¿Ya tienes cuenta? "}
        <Link className="text-brand underline" href={isLogin ? "/register" : "/login"}>
          {isLogin ? "Regístrate" : "Ingresa"}
        </Link>
      </p>
    </div>
  );
}
