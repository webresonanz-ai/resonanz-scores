<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell">
      <div class="section-heading">
        <div class="section-heading-copy">
          <span class="section-eyebrow">
            <i class="bi bi-people-fill"></i>
            Composer Archive
          </span>
          <h2 class="section-title text-gold">Our composers deserve the same elegance as the scores</h2>
          <p class="section-description">
            Profile cards now balance portrait, period, biography, and featured works with more
            readable spacing and calmer surfaces.
          </p>
        </div>
      </div>

      <div class="row">
        <div
          v-for="composer in composerStore.composers"
          :key="composer.id"
          class="col-xl-3 col-md-6 mb-4"
        >
          <div class="composer-card h-100 p-4">
            <div class="text-center">
              <img
                :src="composer.image"
                class="rounded-circle composer-portrait mb-3"
                width="124"
                height="124"
                :alt="composer.name"
              />
              <span class="difficulty-badge mb-3 d-inline-flex">{{ composer.period }}</span>
              <h5 class="text-gold mb-1 composer-title">{{ composer.name }}</h5>
              <p class="text-muted small mb-3">
                <i class="bi bi-geo-alt me-1"></i>{{ composer.nationality }}
              </p>
            </div>
            <p class="composer-biography mb-3">{{ composer.biography }}</p>
            <hr class="border-gold opacity-50" />
            <div class="d-flex justify-content-between align-items-start gap-3">
              <small class="text-muted">
                <i class="bi bi-file-music me-1"></i>{{ composer.works }} works
              </small>
              <small class="text-gold text-end">
                <i class="bi bi-star-fill me-1"></i>{{ composer.featuredWork }}
              </small>
            </div>
          </div>
        </div>
      </div>
    </section>
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

<style scoped>
.composer-portrait {
  margin-inline: auto;
  object-fit: cover;
  border: 3px solid rgba(214, 178, 94, 0.75);
  box-shadow: 0 18px 30px rgba(0, 0, 0, 0.25);
}

.composer-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 1.9rem;
}

.composer-biography {
  color: var(--text-soft);
  font-size: 0.95rem;
}
</style>
