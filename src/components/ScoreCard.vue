<template>
  <div class="score-card h-100">
    <img :src="score.image" class="card-img-top" :alt="score.title" />
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <h5 class="card-title text-gold mb-0">{{ score.title }}</h5>
        <span class="difficulty-badge">{{ score.difficulty }}</span>
      </div>
      <p class="card-text text-muted mb-3"><i class="bi bi-person me-2"></i>{{ score.composer }}</p>
      <p class="card-text text-muted small mb-3">{{ score.description }}</p>
      <div class="d-flex justify-content-between align-items-center">
        <span class="price-tag">${{ score.price }}</span>
        <button class="btn btn-outline-gold btn-sm">
          <i class="bi bi-cart-plus me-1"></i> Add to Cart
        </button>
      </div>
      <div class="mt-2">
        <div class="d-flex align-items-center">
          <i class="bi bi-star-fill text-gold me-1"></i>
          <span class="text-muted small">{{ score.rating }} • {{ score.pages }} pages</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  score: {
    type: Object,
    required: true,
  },
});
</script>
