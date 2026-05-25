<template>
  <div class="container py-5 mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="section-title text-gold mb-0">
        <i class="bi bi-file-earmark-music me-2"></i>Sheet Music Collection
      </h2>
      <ViewToggle />
    </div>

    <!-- Grid View -->
    <div v-if="scoreStore.viewMode === 'grid'" class="row">
      <div v-for="score in scoreStore.scores" :key="score.id" class="col-md-4 mb-4">
        <ScoreCard :score="score" />
      </div>
    </div>

    <!-- List View -->
    <div v-else>
      <div
        v-for="score in scoreStore.scores"
        :key="score.id"
        class="score-list-item mb-3 p-3"
        style="
          background: linear-gradient(135deg, var(--darker) 0%, var(--accent) 100%);
          border: 1px solid rgba(201, 168, 76, 0.3);
          border-radius: 10px;
        "
      >
        <div class="row align-items-center">
          <div class="col-md-2">
            <img
              :src="score.image"
              class="img-fluid rounded"
              style="height: 100px; width: 100%; object-fit: cover"
              :alt="score.title"
            />
          </div>
          <div class="col-md-4">
            <h5 class="text-gold mb-1">{{ score.title }}</h5>
            <p class="text-muted mb-0"><i class="bi bi-person me-1"></i>{{ score.composer }}</p>
          </div>
          <div class="col-md-2">
            <span class="difficulty-badge">{{ score.difficulty }}</span>
          </div>
          <div class="col-md-2">
            <span class="price-tag">${{ score.price }}</span>
          </div>
          <div class="col-md-2 text-end">
            <button class="btn btn-outline-gold btn-sm">
              <i class="bi bi-cart-plus me-1"></i> Add to Cart
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useScoreStore } from "../stores/scoreStore";
import ScoreCard from "../components/ScoreCard.vue";
import ViewToggle from "../components/ViewToggle.vue";

const scoreStore = useScoreStore();
</script>
