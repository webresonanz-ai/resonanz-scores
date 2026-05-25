<template>
  <div class="page-section">
    <HeroSection />
    <div class="container py-5">
      <section class="section-shell mb-5">
        <div class="section-heading">
          <div class="section-heading-copy">
            <span class="section-eyebrow">
              <i class="bi bi-star-fill"></i>
              Signature Selection
            </span>
            <h2 class="section-title text-gold">Featured sheet music with gallery-level presence</h2>
            <p class="section-description">
              Richer cards, calmer spacing, and cleaner hierarchy help visitors focus on the work,
              the composer, and the purchase decision without visual clutter.
            </p>
          </div>
        </div>
        <FeaturedScores />
      </section>

      <section class="section-shell">
        <div class="section-heading">
          <div class="section-heading-copy">
            <span class="section-eyebrow">
              <i class="bi bi-trophy-fill"></i>
              Editorial Spotlight
            </span>
            <h2 class="section-title text-gold">Top composers, framed like a curated collection</h2>
            <p class="section-description">
              More balanced contrast and tighter presentation make biographies, eras, and works
              easier to scan on every screen size.
            </p>
          </div>
        </div>
        <TopComposers />
      </section>
    </div>
  </div>
</template>

<script setup>
import HeroSection from "../components/HeroSection.vue";
import FeaturedScores from "../components/FeaturedScores.vue";
import TopComposers from "../components/TopComposers.vue";
</script>
