<template>
  <div class="composer-card text-center h-100">
    <div class="p-3">
      <img
        :src="composer.image"
        class="rounded-circle mb-3"
        width="100"
        height="100"
        style="object-fit: cover; border: 3px solid var(--gold)"
        :alt="composer.name"
      />
      <h6 class="text-gold mb-1">{{ composer.name }}</h6>
      <p class="text-muted small mb-2">{{ composer.period }}</p>
      <p class="text-muted small mb-0">
        <i class="bi bi-file-music me-1"></i>{{ composer.works }} works
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
