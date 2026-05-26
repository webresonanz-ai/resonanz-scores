import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useScoreApprovalStore = defineStore("scoreApprovals", () => {
  const pendingScores = ref([]);
  const loading = ref(false);
  const reviewingId = ref(0);
  const error = ref("");

  async function fetchPending(token) {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/admin/score-approvals", { token });
      pendingScores.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
      pendingScores.value = [];
    } finally {
      loading.value = false;
    }
  }

  async function review(token, scoreId, action) {
    reviewingId.value = scoreId;
    error.value = "";

    try {
      const response = await apiRequest(`/admin/score-approvals/${action}`, {
        method: "POST",
        token,
        body: { scoreId },
      });
      pendingScores.value = pendingScores.value.filter((score) => score.id !== scoreId);
      return response;
    } catch (reviewError) {
      error.value = reviewError.message;
      throw reviewError;
    } finally {
      reviewingId.value = 0;
    }
  }

  function reset() {
    pendingScores.value = [];
    error.value = "";
  }

  return {
    pendingScores,
    loading,
    reviewingId,
    error,
    fetchPending,
    review,
    reset,
  };
});
