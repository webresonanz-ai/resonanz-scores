<template>
  <div class="container py-5 mt-5">
    <h2 class="section-title text-gold mb-4">
      <i class="bi bi-people-fill me-2"></i>Our Composers
    </h2>

    <div class="row">
      <div
        v-for="composer in composerStore.composers"
        :key="composer.id"
        class="col-lg-3 col-md-4 col-sm-6 mb-4"
      >
        <div class="composer-card h-100 p-4 text-center">
          <img
            :src="composer.image"
            class="rounded-circle mb-3"
            width="120"
            height="120"
            style="object-fit: cover; border: 3px solid var(--gold)"
            :alt="composer.name"
          />
          <h5 class="text-gold mb-1">{{ composer.name }}</h5>
          <p class="text-muted small mb-2">
            <i class="bi bi-geo-alt me-1"></i>{{ composer.nationality }}
          </p>
          <span class="difficulty-badge mb-2 d-inline-block">{{ composer.period }}</span>
          <p class="text-muted small mt-2 mb-2">{{ composer.biography }}</p>
          <hr class="border-gold" />
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
              <i class="bi bi-file-music me-1"></i>{{ composer.works }} works
            </small>
            <small class="text-gold">
              <i class="bi bi-star-fill me-1"></i>{{ composer.featuredWork }}
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import { useComposerStore } from "../stores/composerStore";

const composerStore = useComposerStore();

onMounted(() => {
  if (!composerStore.composers.length) {
    composerStore.fetchComposers();
  }
});
</script>
