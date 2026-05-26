const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api";

function buildHeaders(token, body) {
  const headers = {};

  if (!(body instanceof FormData)) {
    headers["Content-Type"] = "application/json";
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }

  return headers;
}

export async function apiRequest(path, options = {}) {
  const body = options.body instanceof FormData ? options.body : options.body ? JSON.stringify(options.body) : undefined;
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: options.method || "GET",
    headers: buildHeaders(options.token, options.body),
    body,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.message || "Request failed.");
  }

  return data;
}

export async function fetchPreviewBinary(path) {
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: "GET",
    headers: {
      "X-Preview-Request": "1",
    },
  });

  if (!response.ok) {
    const data = await response.json().catch(() => ({}));
    throw new Error(data.message || "Request failed.");
  }

  return response.arrayBuffer();
}

export async function downloadScorePdf(scoreId, filename, token) {
  const response = await fetch(`${API_BASE_URL}/scores/pdf-download?id=${scoreId}`, {
    method: "GET",
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  });

  if (!response.ok) {
    const data = await response.json().catch(() => ({}));
    throw new Error(data.message || "Failed to download PDF.");
  }

  const blob = await response.blob();
  const objectUrl = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = objectUrl;
  link.download = filename?.endsWith(".pdf") ? filename : `${filename || "score"}.pdf`;
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(objectUrl);
}

export { API_BASE_URL };
