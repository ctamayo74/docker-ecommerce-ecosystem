const money = new Intl.NumberFormat("es-US", { style: "currency", currency: "USD" });
export const formatMoney = (n: number) => money.format(n);

export const formatDate = (iso: string | null) =>
  iso
    ? new Intl.DateTimeFormat("es-US", { dateStyle: "medium", timeStyle: "short" }).format(new Date(iso))
    : "";

export const STATUS_LABEL: Record<string, string> = {
  pending: "Pendiente de pago",
  paid: "Pagada",
  failed: "Pago fallido",
};
