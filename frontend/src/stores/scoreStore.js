import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useScoreStore = defineStore("scores", () => {
  const scores = ref([]);
  const myScores = ref([]);
  const viewMode = ref("grid");
  const loading = ref(false);
  const error = ref("");
  const submitting = ref(false);

  function toggleView() {
    viewMode.value = viewMode.value === "grid" ? "list" : "grid";
  }

  async function fetchScores() {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/scores");
      scores.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
    } finally {
      loading.value = false;
    }
  }

  async function fetchMyScores(token) {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer/scores", {
        token,
      });
      myScores.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
      throw fetchError;
    } finally {
      loading.value = false;
    }
  }

  async function createScore(token, payload) {
    submitting.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer/scores", {
        method: "POST",
        token,
        body: payload,
      });
      myScores.value = [response.data, ...myScores.value];
      scores.value = [response.data, ...scores.value];

      return response.data;
    } catch (submitError) {
      error.value = submitError.message;
      throw submitError;
    } finally {
      submitting.value = false;
    }
  }

  return {
    scores,
    myScores,
    viewMode,
    loading,
    error,
    submitting,
    toggleView,
    fetchScores,
    fetchMyScores,
    createScore,
  };
});
