import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useComposerStore = defineStore("composers", () => {
  const composers = ref([]);
  const loading = ref(false);
  const error = ref("");

  async function fetchComposers() {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composers");
      composers.value = response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
    } finally {
      loading.value = false;
    }
  }

  return { composers, loading, error, fetchComposers };
});
