<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell">
      <div class="section-heading">
        <div class="section-heading-copy">
          <span class="section-eyebrow">
            <i class="bi bi-cart3"></i>
            Cart summary
          </span>
          <h2 class="section-title text-gold">Review your scores before checkout</h2>
          <p class="section-description">
            Confirm your selected sheet music, then create an order with pending status and waiting
            payment.
          </p>
        </div>
      </div>

      <div v-if="!cartStore.items.length" class="empty-state">
        <i class="bi bi-bag-x"></i>
        <p class="mb-3">Your cart is still empty.</p>
        <RouterLink :to="{ name: 'scores' }" class="btn btn-outline-gold px-4 py-2">
          Browse Scores
        </RouterLink>
      </div>

      <div v-else class="row g-4">
        <div class="col-lg-8">
          <div
            v-for="item in cartStore.items"
            :key="item.id"
            class="detail-list-item cart-item mb-3"
          >
            <div class="row align-items-center g-3">
              <div class="col-md-2">
                <img :src="item.image" :alt="item.title" class="cart-image" />
              </div>
              <div class="col-md-5">
                <h5 class="text-gold mb-1">{{ item.title }}</h5>
                <p class="text-muted mb-1">
                  <i class="bi bi-person me-1"></i>{{ item.composer }}
                </p>
                <div class="d-flex gap-2 flex-wrap">
                  <span class="difficulty-badge">{{ item.difficulty }}</span>
                  <span class="meta-chip">
                    <i class="bi bi-journal-richtext text-gold"></i>
                    {{ item.pages }} pages
                  </span>
                </div>
              </div>
              <div class="col-md-3 text-md-center">
                <span class="price-tag">${{ formatPrice(item.price) }}</span>
              </div>
              <div class="col-md-2 text-md-end">
                <button
                  class="btn btn-outline-gold btn-sm px-3 py-2"
                  type="button"
                  @click="cartStore.removeFromCart(item.id)"
                >
                  <i class="bi bi-trash3 me-1"></i>
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4">
          <div class="surface-card summary-card p-4">
            <span class="section-eyebrow mb-3">
              <i class="bi bi-receipt"></i>
              Checkout
            </span>
            <h3 class="summary-title text-gold">Order summary</h3>

            <div class="summary-row">
              <span class="text-muted">Items</span>
              <strong>{{ cartStore.itemCount }}</strong>
            </div>
            <div class="summary-row">
              <span class="text-muted">Order status</span>
              <strong>pending</strong>
            </div>
            <div class="summary-row">
              <span class="text-muted">Payment status</span>
              <strong>waiting_payment</strong>
            </div>
            <div class="summary-row total-row">
              <span>Total</span>
              <span class="price-tag">${{ formatPrice(cartStore.subtotal) }}</span>
            </div>

            <p v-if="cartStore.successMessage" class="text-success small mt-3 mb-0">
              {{ cartStore.successMessage }}
            </p>
            <p v-if="cartStore.error" class="text-danger small mt-3 mb-0">
              {{ cartStore.error }}
            </p>
            <p v-if="checkoutMessage" class="text-success small mt-3 mb-0">
              {{ checkoutMessage }}
            </p>

            <button
              class="btn btn-outline-gold w-100 mt-4"
              type="button"
              :disabled="cartStore.loading"
              @click="proceedCheckout"
            >
              {{ cartStore.loading ? "Creating order..." : "Proceed to Checkout" }}
            </button>

            <RouterLink :to="{ name: 'scores' }" class="btn btn-link text-muted w-100 mt-2">
              Continue shopping
            </RouterLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import { useAuthStore } from "../stores/authStore";
import { useCartStore } from "../stores/cartStore";

const authStore = useAuthStore();
const cartStore = useCartStore();
const router = useRouter();
const checkoutMessage = ref("");

function formatPrice(value) {
  return Number(value || 0).toFixed(2);
}

async function proceedCheckout() {
  checkoutMessage.value = "";

  if (!authStore.isAuthenticated) {
    cartStore.error = "Please login before checkout.";
    router.push({ name: "profile" });
    return;
  }

  try {
    const createdOrder = await cartStore.checkout(authStore.token);
    await authStore.fetchProfile();
    checkoutMessage.value = `Order #${createdOrder.id} created with status ${createdOrder.status} and payment ${createdOrder.paymentStatus}.`;
  } catch {
    return;
  }
}
</script>

<style scoped>
.cart-item {
  padding: 1.15rem 1.25rem;
}

.cart-image {
  width: 100%;
  height: 7rem;
  object-fit: cover;
  border-radius: 16px;
  border: 1px solid rgba(214, 178, 94, 0.16);
}

.summary-card {
  position: sticky;
  top: 7rem;
}

.summary-title {
  margin-bottom: 1.5rem;
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.9rem 0;
  border-bottom: 1px solid rgba(214, 178, 94, 0.14);
}

.total-row {
  align-items: center;
  margin-top: 0.4rem;
  padding-top: 1.2rem;
  border-bottom: 0;
  font-weight: 800;
}

.empty-state {
  display: grid;
  place-items: center;
  gap: 0.85rem;
  min-height: 18rem;
  text-align: center;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 2rem;
  color: var(--gold-soft);
}
</style>
