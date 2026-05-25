<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell">
      <div class="section-heading">
        <div class="section-heading-copy">
          <span class="section-eyebrow">
            <i class="bi bi-file-earmark-music-fill"></i>
            Catalog
          </span>
          <h2 class="section-title text-gold">Sheet music collection designed for confident browsing</h2>
          <p class="section-description">
            Each card now emphasizes title, composer, difficulty, and price with clearer spacing
            and higher contrast so the catalog feels more premium and easier to scan.
          </p>
        </div>
        <ViewToggle />
      </div>

      <div v-if="scoreStore.viewMode === 'grid'" class="row">
        <div v-for="score in scoreStore.scores" :key="score.id" class="col-xl-4 col-md-6 mb-4">
          <ScoreCard :score="score" />
        </div>
      </div>

      <div
        v-else
        v-for="score in scoreStore.scores"
        :key="score.id"
        class="detail-list-item mb-3"
      >
        <div class="row align-items-center">
          <div class="col-md-2 mb-3 mb-md-0">
            <img
              :src="score.image"
              class="img-fluid rounded-4 score-list-image"
              :alt="score.title"
            />
          </div>
          <div class="col-md-4 mb-3 mb-md-0">
            <h5 class="text-gold mb-1 list-title">{{ score.title }}</h5>
            <p class="text-muted mb-0"><i class="bi bi-person me-1"></i>{{ score.composer }}</p>
          </div>
          <div class="col-md-2 mb-3 mb-md-0">
            <span class="difficulty-badge">{{ score.difficulty }}</span>
          </div>
          <div class="col-md-2 mb-3 mb-md-0">
            <span class="price-tag">${{ score.price }}</span>
          </div>
          <div class="col-md-2 text-end">
            <RouterLink
              :to="{ name: 'score-detail', params: { id: score.id } }"
              class="btn btn-outline-gold btn-sm px-3 py-2"
            >
              <i class="bi bi-eye me-1"></i> View Score
            </RouterLink>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { RouterLink } from "vue-router";
import { useScoreStore } from "../stores/scoreStore";
import ScoreCard from "../components/ScoreCard.vue";
import ViewToggle from "../components/ViewToggle.vue";

const scoreStore = useScoreStore();

onMounted(() => {
  if (!scoreStore.scores.length) {
    scoreStore.fetchScores();
  }
});
</script>

<style scoped>
.score-list-image {
  width: 100%;
  height: 110px;
  object-fit: cover;
}

.list-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 1.65rem;
}
</style>
