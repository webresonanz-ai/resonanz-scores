<template>
  <div class="container page-section py-5 mt-5">
    <div v-if="!authStore.isAuthenticated" class="surface-card p-4 p-lg-5 text-center">
      <h3 class="text-gold mb-3">Admin login required</h3>
      <p class="text-muted mb-4">Please sign in with an admin account to review composer requests.</p>
      <router-link class="btn btn-outline-gold" to="/profile">Go to Login</router-link>
    </div>

    <div v-else-if="!authStore.isAdmin" class="surface-card p-4 p-lg-5 text-center">
      <h3 class="text-gold mb-3">Admin access only</h3>
      <p class="text-muted mb-0">
        This page is only available for admin accounts that verify composer requests.
      </p>
    </div>

    <div v-else>
      <div class="surface-card p-4 p-lg-5 mb-4">
        <span class="section-eyebrow mb-3">
          <i class="bi bi-shield-check"></i>
          Admin Review
        </span>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <h2 class="text-gold auth-title mb-2">Composer verification requests</h2>
            <p class="text-muted mb-0">
              Review pending requests and approve or decline each applicant.
            </p>
          </div>
          <button class="btn btn-outline-gold" :disabled="requestStore.loadingRequests" @click="refresh">
            {{ requestStore.loadingRequests ? "Refreshing..." : "Refresh" }}
          </button>
        </div>
      </div>

      <p v-if="requestStore.error" class="text-danger small mb-3">{{ requestStore.error }}</p>

      <div v-if="requestStore.loadingRequests" class="surface-card p-4">
        <p class="text-muted mb-0">Loading pending requests...</p>
      </div>

      <div v-else-if="!requestStore.requests.length" class="surface-card p-4">
        <p class="text-muted mb-0">No pending composer requests right now.</p>
      </div>

      <div v-else class="row g-4">
        <div v-for="request in requestStore.requests" :key="request.id" class="col-12">
          <div class="surface-card p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
              <div>
                <span class="badge request-badge mb-3">Pending</span>
                <h4 class="text-gold mb-2">{{ request.name }}</h4>
                <p class="text-muted mb-2">
                  <i class="bi bi-envelope me-2"></i>{{ request.email }}
                </p>
                <p class="text-muted mb-2">
                  <i class="bi bi-geo-alt me-2"></i>{{ request.location || "Location not set" }}
                </p>
                <p class="text-muted mb-3">
                  <i class="bi bi-clock-history me-2"></i>{{ formatDateTime(request.requestedAt) }}
                </p>
                <p class="text-muted mb-0">{{ request.bio || "Music Enthusiast" }}</p>
              </div>

              <div class="d-flex flex-column gap-2 action-column">
                <button
                  class="btn btn-outline-gold"
                  :disabled="requestStore.reviewingId === request.id"
                  @click="handleReview(request.id, 'approve')"
                >
                  {{ requestStore.reviewingId === request.id ? "Processing..." : "Approve" }}
                </button>
                <button
                  class="btn btn-outline-light"
                  :disabled="requestStore.reviewingId === request.id"
                  @click="handleReview(request.id, 'decline')"
                >
                  {{ requestStore.reviewingId === request.id ? "Processing..." : "Decline" }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, watch } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useComposerRequestStore } from "../stores/composerRequestStore";

const authStore = useAuthStore();
const requestStore = useComposerRequestStore();

onMounted(() => {
  if (authStore.isAdmin) {
    requestStore.fetchPendingRequests(authStore.token);
  }
});

watch(
  () => authStore.isAdmin,
  (isAdmin) => {
    if (isAdmin) {
      requestStore.fetchPendingRequests(authStore.token);
      return;
    }

    requestStore.reset();
  },
);

async function refresh() {
  await requestStore.fetchPendingRequests(authStore.token);
}

async function handleReview(requestId, action) {
  try {
    await requestStore.reviewRequest(authStore.token, requestId, action);
  } catch (error) {
    return error;
  }
}

function formatDateTime(value) {
  return new Date(value).toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
}
</script>

<style scoped>
.auth-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2.4rem;
}

.request-badge {
  background: rgba(214, 178, 94, 0.16);
  border: 1px solid rgba(214, 178, 94, 0.35);
  color: var(--gold-soft);
  font-weight: 600;
}

.action-column {
  min-width: 11rem;
}
</style>
