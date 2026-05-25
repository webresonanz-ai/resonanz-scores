<template>
  <div class="container py-5 mt-5">
    <div v-if="!authStore.isAuthenticated" class="row g-4">
      <div class="col-lg-6">
        <div
          class="p-4 h-100"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <h4 class="text-gold mb-4"><i class="bi bi-box-arrow-in-right me-2"></i>Login</h4>
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
        <div
          class="p-4 h-100"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <h4 class="text-gold mb-4"><i class="bi bi-person-plus me-2"></i>Create Account</h4>
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
        <div
          class="profile-sidebar p-4"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <div class="text-center mb-4">
            <img
              :src="authStore.user?.avatar || 'https://picsum.photos/150/150?random=100'"
              class="rounded-circle mb-3"
              width="120"
              height="120"
              style="border: 3px solid var(--gold); object-fit: cover"
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
              <i class="bi bi-calendar text-gold me-2"></i>
              <span class="text-muted">Member since {{ memberSince }}</span>
            </li>
          </ul>
          <button class="btn btn-outline-gold w-100" @click="authStore.logout()">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
          </button>
        </div>
      </div>

      <div class="col-lg-8">
        <div
          class="p-4"
          style="
            background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
            border: 1px solid rgba(201, 168, 76, 0.3);
            border-radius: 15px;
          "
        >
          <h4 class="text-gold mb-4"><i class="bi bi-clock-history me-2"></i>Purchase History</h4>

          <div
            v-for="purchase in authStore.purchases"
            :key="purchase.id"
            class="purchase-item mb-3 p-3"
            style="background: rgba(201, 168, 76, 0.05); border-radius: 10px"
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
import { computed, reactive } from "vue";
import { useAuthStore } from "../stores/authStore";

const authStore = useAuthStore();

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
</script>
