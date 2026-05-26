import { API_BASE_URL } from "./api.js";

let activePaymentContext = null;

function buildAuthHeaders(token) {
  const headers = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  return headers;
}

function splitCustomerName(fullName) {
  const trimmed = String(fullName || "").trim();

  if (!trimmed) {
    return { first_name: "Customer", last_name: "" };
  }

  const parts = trimmed.split(/\s+/);
  const first_name = parts.shift() || "Customer";
  const last_name = parts.join(" ");

  return { first_name, last_name };
}

function buildCheckoutPayload(order, user) {
  const billing = order.billing_info || {};
  const customerName = billing.name || order.billing_name || user?.name || "Customer";
  const { first_name, last_name } = splitCustomerName(customerName);

  return {
    transaction_details: {
      order_id: order.order_number || order.orderNumber,
      gross_amount: Math.round(Number(order.total_amount ?? order.totalAmount ?? 0)),
    },
    credit_card: {
      secure: true,
    },
    customer_details: {
      first_name,
      last_name,
      email: billing.email || order.billing_email || user?.email || "",
      phone: billing.phone || order.billing_phone || "",
    },
  };
}

async function requestPaymentCheckout(token, payload) {
  const response = await fetch(`${API_BASE_URL}/payments/checkout`, {
    method: "POST",
    headers: buildAuthHeaders(token),
    body: JSON.stringify(payload),
  });
  const result = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(result?.error || result?.message || `Payment failed (${response.status})`);
  }

  if (!result.redirect_url) {
    throw new Error("Payment redirect URL is missing");
  }

  return result.redirect_url;
}

export async function fetchOrderDetail(orderId, token) {
  const response = await fetch(`${API_BASE_URL}/orders/detail?id=${orderId}`, {
    method: "GET",
    headers: buildAuthHeaders(token),
  });
  const payload = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(payload?.error || payload?.message || "Failed to fetch order detail");
  }

  return payload?.data ?? payload;
}

export async function fetchOrderByNumber(orderNumber, token) {
  const params = new URLSearchParams({ order_number: orderNumber });
  const response = await fetch(`${API_BASE_URL}/orders/by-number?${params.toString()}`, {
    method: "GET",
    headers: buildAuthHeaders(token),
  });
  const payload = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(payload?.error || payload?.message || `Failed to fetch order ${orderNumber}`);
  }

  return payload?.data ?? payload;
}

export async function cancelOrder(orderId, token) {
  const response = await fetch(`${API_BASE_URL}/orders/cancel`, {
    method: "POST",
    headers: buildAuthHeaders(token),
    body: JSON.stringify({ order_id: orderId }),
  });
  const payload = await response.json().catch(() => null);

  if (!response.ok) {
    throw new Error(payload?.error || payload?.message || "Failed to cancel order");
  }

  return payload?.data ?? payload;
}

export async function updateMidtransPaymentStatus(orderNumber, token) {
  const result = await checkPaymentStatus(orderNumber, token);
  const midtrans = result?.midtrans ?? result;
  const order = result?.order ?? null;

  return {
    midtrans,
    order,
    transaction_status: midtrans?.transaction_status ?? null,
  };
}

export async function checkPaymentStatus(orderNumber, token) {
  const params = new URLSearchParams({ order_id: orderNumber });
  const response = await fetch(`${API_BASE_URL}/payments/midtrans-status?${params.toString()}`, {
    method: "GET",
    headers: buildAuthHeaders(token),
  });
  const result = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(result?.error || result?.message || `Status check failed (${response.status})`);
  }

  return result;
}

function handlePaymentMessage(event) {
  if (!activePaymentContext) {
    return;
  }

  const modal = document.getElementById("payment-modal");
  if (!modal) {
    return;
  }

  const data = event.data;
  const statusCode =
    (typeof data === "object" && data !== null && (data.status_code || data.transaction_status)) ||
    (typeof data === "string" && data);

  if (!statusCode) {
    return;
  }

  const normalized = String(statusCode).toLowerCase();

  if (["200", "201", "settlement", "capture", "success"].includes(normalized)) {
    closePaymentModal(activePaymentContext.orderId);
    return;
  }

  if (["deny", "cancel", "expire", "failure", "failed"].includes(normalized)) {
    closePaymentModal(activePaymentContext.orderId);
  }
}

export async function closePaymentModal(orderId) {
  const modal = document.getElementById("payment-modal");
  if (!modal) {
    return null;
  }

  const context = activePaymentContext;
  const orderNumber = modal.dataset.orderNumber || context?.orderNumber || "";

  modal.remove();
  window.removeEventListener("message", handlePaymentMessage);
  activePaymentContext = null;

  if (!orderNumber || !context?.token) {
    return null;
  }

  try {
    const result = await checkPaymentStatus(orderNumber, context.token);
    context.onComplete?.(result);
    return result;
  } catch (error) {
    context.onError?.(error);
    throw error;
  }
}

export async function openPaymentModal(order, options = {}) {
  const { token, user = null, onComplete, onError } = options;
  const orderId = order.order_id ?? order.id;
  const orderNumber = order.order_number ?? order.orderNumber;

  if (!token) {
    throw new Error("Authentication is required for payment.");
  }

  if (!orderNumber) {
    throw new Error("Order number is missing.");
  }

  const inputOrder = buildCheckoutPayload(order, user);
  let redirectUrl;

  try {
    redirectUrl = await requestPaymentCheckout(token, inputOrder);
  } catch (error) {
    console.error("Payment error:", error);
    onError?.(error);
    throw error;
  }

  const existingModal = document.getElementById("payment-modal");
  if (existingModal) {
    existingModal.remove();
    window.removeEventListener("message", handlePaymentMessage);
  }

  activePaymentContext = {
    orderId,
    orderNumber,
    token,
    onComplete,
    onError,
  };

  const modal = document.createElement("div");
  modal.id = "payment-modal";
  modal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
  `;

  modal.innerHTML = `
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 8px; overflow: hidden;">
      <div style="padding: 16px; background: #f8f9fa; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
        <h3 style="margin: 0; font-size: 18px; color: #212529;">Complete Payment</h3>
        <button id="close-modal" type="button" style="background: none; border: none; font-size: 32px; line-height: 1; cursor: pointer; color: #666;">&times;</button>
      </div>
      <iframe
        id="payment-iframe"
        src="${redirectUrl}"
        style="width: 100%; height: 600px; border: none;"
        title="Payment Gateway"
      ></iframe>
      <div style="padding: 16px; text-align: center; background: #f8f9fa; border-top: 1px solid #dee2e6;">
        <small style="color: #666;">Payment securely processed by Midtrans</small>
      </div>
    </div>
  `;

  document.body.appendChild(modal);
  modal.dataset.orderId = String(orderId ?? "");
  modal.dataset.orderNumber = orderNumber;

  document.getElementById("close-modal").onclick = () => {
    closePaymentModal(orderId);
  };

  window.addEventListener("message", handlePaymentMessage, false);

  return redirectUrl;
}
