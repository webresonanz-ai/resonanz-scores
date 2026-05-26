<template>
  <div class="score-card h-100">
    <img :src="score.image" class="card-img-top" :alt="score.title" />
    <div class="card-body p-4 d-flex flex-column">
      <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
        <h5 class="card-title text-gold mb-0 flex-grow-1">{{ score.title }}</h5>
        <span class="difficulty-badge">{{ score.difficulty }}</span>
      </div>
      <p class="card-text text-muted mb-3">
        <i class="bi bi-person me-2"></i>{{ score.composer }}
      </p>
      <p class="card-text score-description mb-4">{{ score.description }}</p>
      <div class="d-flex align-items-center gap-3 mb-4 score-meta">
        <span class="meta-chip">
          <i class="bi bi-star-fill text-gold"></i>
          {{ score.rating }}
        </span>
        <span class="meta-chip">
          <i class="bi bi-journal-music text-gold"></i>
          {{ score.pages }} pages
        </span>
      </div>
      <div class="d-flex justify-content-between align-items-center mt-auto gap-3">
        <span class="price-tag">{{ formatPrice(score.price) }}</span>
        <RouterLink
          :to="{ name: 'score-detail', params: { id: score.id } }"
          class="btn btn-outline-gold btn-sm px-3 py-2"
        >
          <i class="bi bi-eye me-1"></i> View Score
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from "vue-router";
import { formatPrice } from "../lib/currency.js";

defineProps({
  score: {
    type: Object,
    required: true,
  },
});
</script>

<style scoped>
.card-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 1.6rem;
  line-height: 1.05;
}

.score-description {
  color: var(--text-soft);
  font-size: 0.95rem;
}

.score-meta {
  flex-wrap: wrap;
}

.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.05);
  color: var(--text-muted);
  font-size: 0.82rem;
  font-weight: 700;
}
</style>
