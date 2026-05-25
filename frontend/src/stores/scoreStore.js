import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useScoreStore = defineStore("scores", () => {
  const scores = ref([]);
  const viewMode = ref("grid");
  const loading = ref(false);
  const error = ref("");

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

  return { scores, viewMode, loading, error, toggleView, fetchScores };
});
