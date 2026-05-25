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
          <h4 class="text-gold auth-title mb-4">Purchase history</h4>

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
              <span class="price-tag">${{ purchase.price }}</span>
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
import { computed, onMounted, reactive, watch } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useComposerRequestStore } from "../stores/composerRequestStore";

const authStore = useAuthStore();
const requestStore = useComposerRequestStore();

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
