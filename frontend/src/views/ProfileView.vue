<template>
  <div class="container page-section py-5 mt-5">
    <div v-if="!authStore.isAuthenticated" class="row g-4">
      <div class="col-lg-6">
        <div class="surface-card p-4 p-lg-5 h-100">
          <span class="section-eyebrow mb-3">
            <i class="bi bi-box-arrow-in-right"></i>
            Welcome Back
          </span>
          <h4 class="text-gold auth-title mb-3">Login to continue your collection</h4>
          <p class="text-muted mb-4">
            A cleaner, higher-contrast form keeps the experience calm and readable while you sign
            in.
          </p>
          <form @submit.prevent="submitLogin">
            <div class="mb-3">
              <label class="form-label text-muted">Email</label>
              <input v-model="loginForm.email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Password</label>
              <input v-model="loginForm.password" type="password" class="form-control" required />
            </div>
            <p v-if="authStore.error" class="text-danger small mb-3">{{ authStore.error }}</p>
            <button class="btn btn-outline-gold w-100" :disabled="authStore.loading">
              {{ authStore.loading ? "Signing in..." : "Login" }}
            </button>
          </form>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="surface-card p-4 p-lg-5 h-100">
          <span class="section-eyebrow mb-3">
            <i class="bi bi-person-plus"></i>
            New Member
          </span>
          <h4 class="text-gold auth-title mb-3">Create an account with a polished first impression</h4>
          <p class="text-muted mb-4">
            Clear input styling and more breathing room help the form feel professional instead of
            dense.
          </p>
          <form @submit.prevent="submitRegister">
            <div class="mb-3">
              <label class="form-label text-muted">Name</label>
              <input v-model="registerForm.name" type="text" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Email</label>
              <input v-model="registerForm.email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Location</label>
              <input v-model="registerForm.location" type="text" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label text-muted">Password</label>
              <input v-model="registerForm.password" type="password" class="form-control" required />
            </div>
            <button class="btn btn-outline-gold w-100" :disabled="authStore.loading">
              {{ authStore.loading ? "Creating account..." : "Register" }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <div v-else class="row">
      <div class="col-lg-4">
        <div class="profile-sidebar surface-card p-4">
          <div class="text-center mb-4">
            <img
              :src="authStore.user?.avatar || 'https://picsum.photos/150/150?random=100'"
              class="rounded-circle profile-avatar mb-3"
              width="120"
              height="120"
              alt="Profile"
            />
            <h4 class="text-gold">{{ authStore.user?.name }}</h4>
            <p class="text-muted">{{ authStore.user?.bio || "Music Enthusiast" }}</p>
          </div>
          <hr class="border-gold" />
          <ul class="list-unstyled">
            <li class="mb-3">
              <i class="bi bi-envelope text-gold me-2"></i>
              <span class="text-muted">{{ authStore.user?.email }}</span>
            </li>
            <li class="mb-3">
              <i class="bi bi-geo-alt text-gold me-2"></i>
              <span class="text-muted">{{ authStore.user?.location || "Not set yet" }}</span>
            </li>
            <li class="mb-3">
              <i class="bi bi-person-badge text-gold me-2"></i>
              <span class="text-muted text-capitalize">{{ authStore.user?.role || "customer" }}</span>
            </li>
            <li class="mb-3">
              <i class="bi bi-calendar text-gold me-2"></i>
              <span class="text-muted">Member since {{ memberSince }}</span>
            </li>
          </ul>
          <button class="btn btn-outline-gold w-100" @click="authStore.logout()">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </button>
          <button
            v-if="showComposerRequestButton"
            class="btn btn-outline-light w-100 mt-3"
            :disabled="requestStore.submitting"
            @click="submitComposerRequest"
          >
            {{ requestStore.submitting ? "Submitting request..." : "Request to Become Composer" }}
          </button>
          <div v-if="composerRequestLabel" class="request-status mt-3">
            <span class="small text-uppercase text-gold d-block mb-1">Composer Request Status</span>
            <span class="text-muted">{{ composerRequestLabel }}</span>
          </div>
          <p v-if="requestStore.error" class="text-danger small mt-3 mb-0">{{ requestStore.error }}</p>
        </div>
      </div>

      <div class="col-lg-8">
        <div class="surface-card p-4 p-lg-5">
          <span class="section-eyebrow mb-3">
            <i class="bi bi-clock-history"></i>
            Account Activity
          </span>
          <h4 class="text-gold auth-title mb-4">Recent orders</h4>

          <p v-if="orderActionMessage" class="small mb-3" :class="orderActionError ? 'text-danger' : 'text-success'">
            {{ orderActionMessage }}
          </p>

          <div
            v-for="order in authStore.orders"
            :key="`order-${order.id}`"
            class="purchase-item detail-list-item mb-3"
          >
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
              <div class="flex-grow-1">
                <h6 class="text-gold mb-1">
                  Order #{{ order.id }}
                  <span v-if="order.orderNumber" class="text-muted fw-normal small">
                    ({{ order.orderNumber }})
                  </span>
                </h6>
                <p class="text-muted mb-1 small">
                  {{ order.totalItems }} item(s) • {{ formatDate(order.createdAt) }}
                </p>
                <div class="d-flex gap-2 flex-wrap mb-3">
                  <span class="difficulty-badge text-uppercase">{{ order.status }}</span>
                  <span class="meta-chip">
                    <i class="bi bi-credit-card text-gold"></i>
                    {{ order.paymentStatus }}
                  </span>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                  <button
                    type="button"
                    class="btn btn-outline-gold btn-sm px-3 py-2"
                    :disabled="isOrderBusy(order.id)"
                    @click="refreshOrderPayment(order)"
                  >
                    <i class="bi bi-arrow-clockwise me-1"></i>
                    {{ orderBusyLabel(order.id, "refresh") }}
                  </button>

                  <template v-if="isAwaitingPayment(order)">
                    <button
                      type="button"
                      class="btn btn-outline-gold btn-sm px-3 py-2"
                      :disabled="isOrderBusy(order.id)"
                      @click="reprocessPayment(order)"
                    >
                      <i class="bi bi-credit-card me-1"></i>
                      {{ orderBusyLabel(order.id, "pay") }}
                    </button>
                    <button
                      type="button"
                      class="btn btn-outline-light btn-sm px-3 py-2"
                      :disabled="isOrderBusy(order.id)"
                      @click="cancelPendingOrder(order)"
                    >
                      <i class="bi bi-x-circle me-1"></i>
                      {{ orderBusyLabel(order.id, "cancel") }}
                    </button>
                  </template>
                </div>
              </div>
              <span class="price-tag">{{ formatPrice(order.totalAmount) }}</span>
            </div>
          </div>

          <p v-if="!authStore.orders.length" class="text-muted mb-4">
            No orders yet. Your checkout activity will appear here.
          </p>

          <h4 class="text-gold auth-title mb-4 mt-5">Purchase history</h4>

          <div
            v-for="purchase in authStore.purchases"
            :key="purchase.id"
            class="purchase-item detail-list-item mb-3"
          >
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h6 class="text-gold mb-1">{{ purchase.title }}</h6>
                <p class="text-muted mb-0 small">Purchased on {{ formatDate(purchase.purchaseDate) }}</p>
              </div>
              <span class="price-tag">{{ formatPrice(purchase.price) }}</span>
            </div>
          </div>

          <p v-if="!authStore.purchases.length" class="text-muted mb-0">
            No purchases yet. Your future orders can appear here.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useComposerRequestStore } from "../stores/composerRequestStore";
import { formatPrice } from "../lib/currency.js";
import {
  cancelOrder,
  openPaymentModal,
  updateMidtransPaymentStatus,
} from "../lib/payment.js";

const authStore = useAuthStore();
const requestStore = useComposerRequestStore();
const orderActionMessage = ref("");
const orderActionError = ref(false);
const orderBusy = reactive({});

const loginForm = reactive({
  email: "john.doe@email.com",
  password: "password123",
});

const registerForm = reactive({
  name: "",
  email: "",
  location: "",
  password: "",
});

const memberSince = computed(() => {
  if (!authStore.user?.created_at) {
    return "now";
  }

  return new Date(authStore.user.created_at).getFullYear();
});

const showComposerRequestButton = computed(() => {
  return authStore.isAuthenticated && authStore.user?.role === "customer" && requestStore.myRequest?.status !== "pending";
});

const composerRequestLabel = computed(() => {
  if (authStore.user?.role === "composer") {
    return "Approved. Your account is now a composer account.";
  }

  if (!requestStore.myRequest) {
    return "";
  }

  if (requestStore.myRequest.status === "pending") {
    return "Pending admin review.";
  }

  if (requestStore.myRequest.status === "approved") {
    return "Approved. Please refresh if your role has not updated yet.";
  }

  return "Declined. You can submit a new request anytime.";
});

onMounted(() => {
  if (authStore.isAuthenticated) {
    requestStore.fetchMyRequest(authStore.token);
  }
});

watch(
  () => authStore.isAuthenticated,
  (isAuthenticated) => {
    if (isAuthenticated) {
      requestStore.fetchMyRequest(authStore.token);
      return;
    }

    requestStore.reset();
  },
);

function formatDate(value) {
  return new Date(value).toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

async function submitLogin() {
  try {
    await authStore.login(loginForm);
  } catch (error) {
    return error;
  }
}

async function submitRegister() {
  try {
    await authStore.register(registerForm);
    registerForm.name = "";
    registerForm.email = "";
    registerForm.location = "";
    registerForm.password = "";
  } catch (error) {
    return error;
  }
}

async function submitComposerRequest() {
  try {
    await requestStore.submitRequest(authStore.token);
  } catch (error) {
    return error;
  }
}

function isAwaitingPayment(order) {
  return order.status === "pending" && order.paymentStatus === "waiting_payment";
}

function setOrderBusy(orderId, action, value) {
  if (!orderBusy[orderId]) {
    orderBusy[orderId] = {};
  }
  orderBusy[orderId][action] = value;
}

function isOrderBusy(orderId) {
  const state = orderBusy[orderId];
  return Boolean(state?.refresh || state?.pay || state?.cancel);
}

function orderBusyLabel(orderId, action) {
  const state = orderBusy[orderId];
  if (state?.refresh && action === "refresh") {
    return "Refreshing...";
  }
  if (state?.pay && action === "pay") {
    return "Opening...";
  }
  if (state?.cancel && action === "cancel") {
    return "Cancelling...";
  }

  if (action === "refresh") {
    return "Refresh status";
  }
  if (action === "pay") {
    return "Pay now";
  }
  return "Cancel order";
}

function setOrderFeedback(message, isError = false) {
  orderActionMessage.value = message;
  orderActionError.value = isError;
}

function formatMidtransStatus(status) {
  if (!status) {
    return "Status checked. No Midtrans update yet.";
  }

  return `Midtrans: ${status}`;
}

async function refreshOrderPayment(order) {
  const orderNumber = order.orderNumber;

  if (!orderNumber) {
    setOrderFeedback("This order has no payment reference yet.", true);
    return;
  }

  setOrderBusy(order.id, "refresh", true);
  setOrderFeedback("");

  try {
    const result = await updateMidtransPaymentStatus(orderNumber, authStore.token);
    await authStore.fetchProfile();
    const updated = authStore.orders.find((entry) => entry.id === order.id);
    const transactionStatus = result.transaction_status;
    const paymentStatus = updated?.paymentStatus ?? order.paymentStatus;
    setOrderFeedback(
      `${formatMidtransStatus(transactionStatus)} Order payment is now ${paymentStatus}.`,
    );
  } catch (error) {
    setOrderFeedback(error.message || "Failed to refresh payment status.", true);
  } finally {
    setOrderBusy(order.id, "refresh", false);
  }
}

async function reprocessPayment(order) {
  if (!isAwaitingPayment(order)) {
    return;
  }

  const orderNumber = order.orderNumber;

  if (!orderNumber) {
    setOrderFeedback("This order has no payment reference yet.", true);
    return;
  }

  setOrderBusy(order.id, "pay", true);
  setOrderFeedback("");

  try {
    await openPaymentModal(
      {
        id: order.id,
        orderNumber,
        totalAmount: order.totalAmount,
        billing_name: authStore.user?.name,
        billing_email: authStore.user?.email,
      },
      {
        token: authStore.token,
        user: authStore.user,
        onComplete: async (result) => {
          await authStore.fetchProfile();
          const updated = result?.order ?? order;
          setOrderFeedback(
            `Payment updated for ${orderNumber}. Status: ${updated.status}, payment: ${updated.paymentStatus}.`,
          );
        },
        onError: (error) => {
          setOrderFeedback(error.message || "Payment could not be started.", true);
        },
      },
    );
  } catch (error) {
    setOrderFeedback(error.message || "Payment could not be started.", true);
  } finally {
    setOrderBusy(order.id, "pay", false);
  }
}

async function cancelPendingOrder(order) {
  if (!isAwaitingPayment(order)) {
    return;
  }

  const confirmed = window.confirm(`Cancel order #${order.id}? This cannot be undone.`);

  if (!confirmed) {
    return;
  }

  setOrderBusy(order.id, "cancel", true);
  setOrderFeedback("");

  try {
    await cancelOrder(order.id, authStore.token);
    await authStore.fetchProfile();
    setOrderFeedback(`Order #${order.id} has been cancelled.`);
  } catch (error) {
    setOrderFeedback(error.message || "Failed to cancel order.", true);
  } finally {
    setOrderBusy(order.id, "cancel", false);
  }
}
</script>

<style scoped>
.auth-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2.2rem;
}

.profile-avatar {
  margin-inline: auto;
  border: 3px solid rgba(214, 178, 94, 0.78);
  object-fit: cover;
  box-shadow: 0 18px 32px rgba(0, 0, 0, 0.24);
}

.request-status {
  padding-top: 0.9rem;
  border-top: 1px solid rgba(214, 178, 94, 0.2);
}
</style>
