export const CURRENCY_CODE = "IDR";

export function formatPrice(value) {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: CURRENCY_CODE,
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(Number(value || 0));
}
