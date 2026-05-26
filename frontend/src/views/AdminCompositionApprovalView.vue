<template>
  <div class="container page-section py-5 mt-5">
    <div v-if="!authStore.isAuthenticated" class="surface-card p-4 p-lg-5 text-center">
      <h3 class="text-gold mb-3">Staff login required</h3>
      <p class="text-muted mb-4">Please sign in with an admin or manager account to review compositions.</p>
      <router-link class="btn btn-outline-gold" to="/profile">Go to Login</router-link>
    </div>

    <div v-else-if="!authStore.isStaff" class="surface-card p-4 p-lg-5 text-center">
      <h3 class="text-gold mb-3">Staff access only</h3>
      <p class="text-muted mb-0">
        This page is only available for admin and manager accounts that approve uploaded compositions.
      </p>
    </div>

    <div v-else>
      <div class="surface-card p-4 p-lg-5 mb-4">
        <span class="section-eyebrow mb-3">
          <i class="bi bi-clipboard-check"></i>
          Composition Review
        </span>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
          <div>
            <h2 class="text-gold auth-title mb-2">Pending composition uploads</h2>
            <p class="text-muted mb-0">
              Approve compositions to publish them in the catalog, or decline and notify the composer by email.
            </p>
          </div>
          <button class="btn btn-outline-gold" :disabled="approvalStore.loading" @click="refresh">
            {{ approvalStore.loading ? "Refreshing..." : "Refresh" }}
          </button>
        </div>
      </div>

      <p v-if="approvalStore.error" class="text-danger small mb-3">{{ approvalStore.error }}</p>

      <div v-if="approvalStore.loading" class="surface-card p-4">
        <p class="text-muted mb-0">Loading pending compositions...</p>
      </div>

      <div v-else-if="!approvalStore.pendingScores.length" class="surface-card p-4">
        <p class="text-muted mb-0">No pending composition uploads right now.</p>
      </div>

      <div v-else class="row g-4">
        <div v-for="score in approvalStore.pendingScores" :key="score.id" class="col-12">
          <div class="surface-card p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
              <div>
                <span class="badge request-badge mb-3">Pending approval</span>
                <h4 class="text-gold mb-2">{{ score.title }}</h4>
                <p class="text-muted mb-2">
                  <i class="bi bi-person me-2"></i>{{ score.composer }}
                  <span v-if="score.isArranged && score.arranger"> · Arranged by {{ score.arranger }}</span>
                </p>
                <p class="text-muted mb-2">
                  <i class="bi bi-envelope me-2"></i>{{ score.submitterEmail }}
                  <span class="ms-2">({{ score.submitterName }})</span>
                </p>
                <p class="text-muted mb-2">
                  <i class="bi bi-clock-history me-2"></i>{{ formatDateTime(score.createdAt) }}
                </p>
                <div class="composition-meta mb-3">
                  <span class="difficulty-badge">{{ score.genre }}</span>
                  <span class="difficulty-badge">{{ score.difficulty }}</span>
                  <span class="difficulty-badge">{{ score.pages }} pages</span>
                  <span class="price-tag">{{ formatPrice(score.price) }}</span>
                </div>
                <p class="text-muted mb-0">{{ score.description }}</p>
              </div>

              <div class="d-flex flex-column gap-2 action-column">
                <button
                  class="btn btn-outline-gold"
                  :disabled="approvalStore.reviewingId === score.id"
                  @click="handleReview(score.id, 'approve')"
                >
                  {{ approvalStore.reviewingId === score.id ? "Processing..." : "Approve" }}
                </button>
                <button
                  class="btn btn-outline-light"
                  :disabled="approvalStore.reviewingId === score.id"
                  @click="handleReview(score.id, 'decline')"
                >
                  {{ approvalStore.reviewingId === score.id ? "Processing..." : "Decline" }}
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
import { useScoreApprovalStore } from "../stores/scoreApprovalStore";
import { formatPrice } from "../lib/currency.js";

const authStore = useAuthStore();
const approvalStore = useScoreApprovalStore();

onMounted(() => {
  if (authStore.isStaff) {
    approvalStore.fetchPending(authStore.token);
  }
});

watch(
  () => authStore.isStaff,
  (isStaff) => {
    if (isStaff) {
      approvalStore.fetchPending(authStore.token);
      return;
    }

    approvalStore.reset();
  },
);

async function refresh() {
  await approvalStore.fetchPending(authStore.token);
}

async function handleReview(scoreId, action) {
  try {
    await approvalStore.review(authStore.token, scoreId, action);
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

.composition-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
  align-items: center;
}

.action-column {
  min-width: 11rem;
}
</style>
