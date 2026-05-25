import { defineStore } from "pinia";
import { ref } from "vue";
import { apiRequest } from "../lib/api";

export const useComposerProfileStore = defineStore("composerProfile", () => {
  const profile = ref(null);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref("");

  async function fetchProfile(token) {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer/profile", { token });
      profile.value = response.data;
      return response.data;
    } catch (fetchError) {
      error.value = fetchError.message;
      profile.value = null;
      throw fetchError;
    } finally {
      loading.value = false;
    }
  }

  async function saveProfile(token, payload) {
    saving.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/composer/profile", {
        method: "POST",
        token,
        body: payload,
      });
      profile.value = response.data;
      return response.data;
    } catch (saveError) {
      error.value = saveError.message;
      throw saveError;
    } finally {
      saving.value = false;
    }
  }

  function reset() {
    profile.value = null;
    error.value = "";
  }

  return {
    profile,
    loading,
    saving,
    error,
    fetchProfile,
    saveProfile,
    reset,
  };
});
