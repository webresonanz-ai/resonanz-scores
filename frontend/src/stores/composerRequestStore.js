import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useComposerRequestStore = defineStore("composerRequests", () => {
  const myRequest = ref(null);
  const requests = ref([]);
  const loadingMyRequest = ref(false);
  const loadingRequests = ref(false);
  const submitting = ref(false);
  const reviewingId = ref(0);
  const error = ref("");

  async function fetchMyRequest(token) {
    loadingMyRequest.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer-requests/me", { token });
      myRequest.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
      myRequest.value = null;
    } finally {
      loadingMyRequest.value = false;
    }
  }

  async function submitRequest(token) {
    submitting.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer-requests", {
        method: "POST",
        token,
      });
      myRequest.value = response.data;
      return response;
    } catch (submitError) {
      error.value = submitError.message;
      throw submitError;
    } finally {
      submitting.value = false;
    }
  }

  async function fetchPendingRequests(token) {
    loadingRequests.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/admin/composer-requests", { token });
      requests.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
      requests.value = [];
    } finally {
      loadingRequests.value = false;
    }
  }

  async function reviewRequest(token, requestId, action) {
    reviewingId.value = requestId;
    error.value = "";

    try {
      const response = await apiRequest(`/admin/composer-requests/${action}`, {
        method: "POST",
        token,
        body: { requestId },
      });
      requests.value = requests.value.filter((request) => request.id !== requestId);
      return response;
    } catch (reviewError) {
      error.value = reviewError.message;
      throw reviewError;
    } finally {
      reviewingId.value = 0;
    }
  }

  function reset() {
    myRequest.value = null;
    requests.value = [];
    error.value = "";
  }

  return {
    myRequest,
    requests,
    loadingMyRequest,
    loadingRequests,
    submitting,
    reviewingId,
    error,
    fetchMyRequest,
    submitRequest,
    fetchPendingRequests,
    reviewRequest,
    reset,
  };
});
