import { defineStore } from "pinia";
import { computed, ref } from "vue";
import { apiRequest } from "../lib/api";
import { useAuthStore } from "./authStore";

const CART_KEY = "sheet-music-store-cart";

function readStoredCart() {
  try {
    const parsed = JSON.parse(localStorage.getItem(CART_KEY) || "[]");
    return Array.isArray(parsed) ? parsed : [];
  } catch {
    return [];
  }
}

export const useCartStore = defineStore("cart", () => {
  const authStore = useAuthStore();
  const items = ref(readStoredCart());
  const loading = ref(false);
  const error = ref("");
  const successMessage = ref("");

  const itemCount = computed(() => items.value.length);
  const subtotal = computed(() =>
    items.value.reduce((total, item) => total + Number(item.price || 0), 0),
  );

  function persist() {
    localStorage.setItem(CART_KEY, JSON.stringify(items.value));
  }

  function addToCart(score) {
    error.value = "";
    successMessage.value = "";

    if (!authStore.isAuthenticated) {
      error.value = "Please login first before adding a score to your cart.";
      return false;
    }

    if (items.value.some((item) => item.id === score.id)) {
      error.value = "This score is already in your cart.";
      return false;
    }

    items.value = [
      ...items.value,
      {
        id: score.id,
        title: score.title,
        composer: score.composer,
        image: score.image,
        difficulty: score.difficulty,
        pages: score.pages,
        price: Number(score.price || 0),
      },
    ];
    persist();
    successMessage.value = "Score added to cart.";
    return true;
  }

  function removeFromCart(scoreId) {
    items.value = items.value.filter((item) => item.id !== scoreId);
    persist();
  }

  function clearCart() {
    items.value = [];
    persist();
  }

  async function checkout(token) {
    loading.value = true;
    error.value = "";
    successMessage.value = "";

    try {
      const response = await apiRequest("/orders", {
        method: "POST",
        token,
        body: {
          items: items.value.map((item) => ({
            score_id: item.id,
          })),
        },
      });

      clearCart();
      successMessage.value = "Order created successfully. Payment is waiting.";
      return response.data;
    } catch (checkoutError) {
      error.value = checkoutError.message;
      throw checkoutError;
    } finally {
      loading.value = false;
    }
  }

  return {
    items,
    loading,
    error,
    successMessage,
    itemCount,
    subtotal,
    addToCart,
    removeFromCart,
    clearCart,
    checkout,
  };
});
