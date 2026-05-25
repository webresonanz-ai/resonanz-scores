<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell">
      <div class="mb-4">
        <RouterLink :to="{ name: 'scores' }" class="back-link">
          <i class="bi bi-arrow-left"></i>
          Back to scores
        </RouterLink>
      </div>

      <div v-if="scoreStore.loading" class="empty-state">
        <i class="bi bi-hourglass-split"></i>
        <p class="mb-0">Loading score details...</p>
      </div>

      <div v-else-if="scoreStore.error" class="empty-state">
        <i class="bi bi-exclamation-diamond"></i>
        <p class="mb-0">{{ scoreStore.error }}</p>
      </div>

      <div v-else-if="score" class="row g-4">
        <div class="col-lg-5">
          <div class="surface-card detail-panel p-4 h-100">
            <img :src="score.image" :alt="score.title" class="detail-cover mb-4" />

            <span class="section-eyebrow mb-3">
              <i class="bi bi-file-earmark-music-fill"></i>
              Score detail
            </span>
            <h1 class="section-title text-gold detail-title">{{ score.title }}</h1>
            <p class="detail-lead mb-4">
              {{ score.description }}
            </p>

            <div class="detail-grid mb-4">
              <div class="meta-block">
                <span class="meta-label">Composer</span>
                <strong>{{ score.composer }}</strong>
              </div>
              <div class="meta-block" v-if="score.is_arranged && score.arranger">
                <span class="meta-label">Arranger</span>
                <strong>{{ score.arranger }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Genre</span>
                <strong>{{ score.genre }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Difficulty</span>
                <strong>{{ score.difficulty }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Pages</span>
                <strong>{{ score.pages }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Rating</span>
                <strong>{{ score.rating }}</strong>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
              <span class="price-tag detail-price">${{ score.price }}</span>
              <button class="btn btn-outline-gold px-4 py-2" type="button" @click="handleAddToCart">
                <i class="bi bi-cart-plus me-2"></i>
                Add to Cart
              </button>
            </div>
            <p v-if="cartStore.successMessage" class="text-success small mb-0 mt-3">
              {{ cartStore.successMessage }}
            </p>
            <p v-if="cartStore.error" class="text-danger small mb-0 mt-2">
              {{ cartStore.error }}
            </p>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="surface-card pdf-panel p-3 p-md-4">
            <div class="pdf-panel-header">
              <div>
                <span class="section-eyebrow mb-2">
                  <i class="bi bi-filetype-pdf"></i>
                  Preview
                </span>
                <h2 class="pdf-title">PDF Preview</h2>
              </div>
              <span class="meta-chip">
                <i class="bi bi-journal-richtext text-gold"></i>
                {{ score.pages }} pages
              </span>
            </div>

            <div v-if="score.pdf_url" class="pdf-frame-wrap">
              <iframe
                :src="score.pdf_url"
                class="pdf-frame"
                :title="`PDF preview for ${score.title}`"
              ></iframe>
            </div>

            <div v-else class="empty-state compact">
              <i class="bi bi-file-earmark-x"></i>
              <p class="mb-0">No PDF preview is available for this score yet.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../stores/authStore";
import { useCartStore } from "../stores/cartStore";
import { useScoreStore } from "../stores/scoreStore";

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const scoreStore = useScoreStore();
const cartStore = useCartStore();

const score = computed(() => scoreStore.currentScore);

async function loadScore() {
  const scoreId = Number(route.params.id);

  if (!Number.isFinite(scoreId) || scoreId <= 0) {
    scoreStore.currentScore = null;
    scoreStore.error = "Score not found.";
    return;
  }

  try {
    await scoreStore.fetchScore(scoreId);
  } catch {
    // Store state already captures the fetch error for the UI.
  }
}

onMounted(loadScore);
watch(() => route.params.id, loadScore);

function handleAddToCart() {
  if (!score.value) {
    return;
  }

  const added = cartStore.addToCart(score.value);

  if (!added && !authStore.isAuthenticated) {
    router.push({ name: "profile" });
  }
}
</script>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  color: var(--gold-soft);
  font-weight: 700;
}

.detail-panel,
.pdf-panel {
  height: 100%;
}

.detail-cover {
  width: 100%;
  max-height: 24rem;
  object-fit: cover;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.18);
}

.detail-title {
  font-size: clamp(2.6rem, 5vw, 4rem);
}

.detail-lead {
  color: var(--text-soft);
  font-size: 1.02rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.meta-block {
  padding: 1rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(214, 178, 94, 0.14);
}

.meta-label {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--text-muted);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.detail-price {
  font-size: 2rem;
}

.pdf-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.pdf-title {
  margin: 0;
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2rem;
  color: var(--text);
}

.pdf-frame-wrap {
  overflow: hidden;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.16);
  background: rgba(4, 8, 15, 0.65);
}

.pdf-frame {
  display: block;
  width: 100%;
  min-height: 72vh;
  border: 0;
}

.empty-state {
  display: grid;
  place-items: center;
  gap: 0.8rem;
  min-height: 18rem;
  text-align: center;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 2rem;
  color: var(--gold-soft);
}

.empty-state.compact {
  min-height: 24rem;
}

@media (max-width: 767.98px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .pdf-panel-header {
    flex-direction: column;
  }

  .pdf-frame {
    min-height: 60vh;
  }
}
</style>
