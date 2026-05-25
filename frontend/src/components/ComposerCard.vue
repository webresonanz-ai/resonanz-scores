<template>
  <div class="composer-card text-center h-100">
    <div class="p-4">
      <img
        :src="composer.image"
        class="rounded-circle composer-avatar mb-3"
        width="110"
        height="110"
        :alt="composer.name"
      />
      <span class="difficulty-badge mb-3">{{ composer.period }}</span>
      <h6 class="text-gold mb-2 composer-name">{{ composer.name }}</h6>
      <p class="text-muted small mb-3">
        <i class="bi bi-file-music me-1"></i>{{ composer.works }} works in catalog
      </p>
      <p class="composer-copy mb-0">
        {{ composer.featuredWork }}
      </p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  composer: {
    type: Object,
    required: true,
  },
});
</script>

<style scoped>
.composer-avatar {
  margin-inline: auto;
  object-fit: cover;
  border: 3px solid rgba(214, 178, 94, 0.8);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
}

.composer-name {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 1.5rem;
}

.composer-copy {
  color: var(--text-soft);
  font-size: 0.92rem;
}
</style>
