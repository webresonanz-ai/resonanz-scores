<template>
  <div>
    <!-- Hero Section -->
    <HeroSection />

    <!-- Featured Scores -->
    <div class="container py-5">
      <h2 class="section-title text-gold mb-4">
        <i class="bi bi-star-fill me-2"></i>Featured Sheet Music
      </h2>
      <FeaturedScores />
    </div>

    <!-- Top Composers -->
    <div class="container py-5">
      <h2 class="section-title text-gold mb-4">
        <i class="bi bi-trophy-fill me-2"></i>Top Composers
      </h2>
      <TopComposers />
    </div>
  </div>
</template>

<script setup>
import HeroSection from "../components/HeroSection.vue";
import FeaturedScores from "../components/FeaturedScores.vue";
import TopComposers from "../components/TopComposers.vue";
</script>
