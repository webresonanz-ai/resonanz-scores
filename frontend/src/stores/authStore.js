import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { apiRequest } from "../lib/api";

const TOKEN_KEY = "sheet-music-store-token";

export const useAuthStore = defineStore("auth", () => {
  const token = ref(localStorage.getItem(TOKEN_KEY) || "");
  const user = ref(null);
  const purchases = ref([]);
  const orders = ref([]);
  const loading = ref(false);
  const error = ref("");

  const isAuthenticated = computed(() => Boolean(token.value && user.value));
  const isAdmin = computed(() => user.value?.role === "admin");
  const isComposer = computed(() => user.value?.role === "composer");

  function persistToken(value) {
    token.value = value || "";

    if (token.value) {
      localStorage.setItem(TOKEN_KEY, token.value);
      return;
    }

    localStorage.removeItem(TOKEN_KEY);
  }

  async function fetchProfile() {
    if (!token.value) {
      user.value = null;
      purchases.value = [];
      orders.value = [];
      return;
    }

    loading.value = true;
    error.value = "";

    try {
      const profileResponse = await apiRequest("/auth/me", {
        token: token.value,
      });
      const purchaseResponse = await apiRequest("/purchases", {
        token: token.value,
      });
      const orderResponse = await apiRequest("/orders", {
        token: token.value,
      });

      user.value = profileResponse.user;
      purchases.value = purchaseResponse.data;
      orders.value = orderResponse.data;
    } catch (fetchError) {
      persistToken("");
      user.value = null;
      purchases.value = [];
      orders.value = [];
      error.value = fetchError.message;
    } finally {
      loading.value = false;
    }
  }

  async function login(credentials) {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/auth/login", {
        method: "POST",
        body: credentials,
      });

      persistToken(response.token);
      user.value = response.user;
      await fetchProfile();
    } catch (loginError) {
      error.value = loginError.message;
      throw loginError;
    } finally {
      loading.value = false;
    }
  }

  async function register(payload) {
    loading.value = true;
    error.value = "";

    try {
      const response = await apiRequest("/auth/register", {
        method: "POST",
        body: payload,
      });

      persistToken(response.token);
      user.value = response.user;
      purchases.value = [];
      orders.value = [];
      await fetchProfile();
    } catch (registerError) {
      error.value = registerError.message;
      throw registerError;
    } finally {
      loading.value = false;
    }
  }

  function logout() {
    persistToken("");
    user.value = null;
    purchases.value = [];
    orders.value = [];
    error.value = "";
  }

  return {
    token,
    user,
    purchases,
    orders,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    isComposer,
    fetchProfile,
    login,
    register,
    logout,
  };
});
