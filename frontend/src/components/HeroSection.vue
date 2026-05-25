<template>
  <section class="hero-section">
    <div class="container">
      <div class="musical-notes">
        <i class="bi bi-music-note note-1"></i>
        <i class="bi bi-music-note-beamed note-2"></i>
        <i class="bi bi-music-note note-3"></i>
      </div>
      <div class="hero-panel">
        <div class="row align-items-center g-5">
          <div class="col-xl-7">
            <span class="section-eyebrow">
              <i class="bi bi-vinyl-fill"></i>
              Curated Classical Library
            </span>
            <h1 class="hero-title">
              The
              <span class="hero-accent">Resonanz</span>
            </h1>
            <p class="hero-subtitle">Where masterworks feel collected, not crowded</p>
            <p class="hero-description">
              Explore premium sheet music with a gallery-like browsing experience, richer detail,
              and polished presentation that helps every title, image, and composer story stay
              easy to read.
            </p>
            <div class="hero-highlights">
              <span class="hero-pill">
                <i class="bi bi-stars text-gold"></i>
                Handpicked editions
              </span>
              <span class="hero-pill">
                <i class="bi bi-headphones text-gold"></i>
                Performer-friendly discovery
              </span>
              <span class="hero-pill">
                <i class="bi bi-shield-check text-gold"></i>
                Clear, elegant catalog design
              </span>
            </div>
            <div class="hero-actions d-flex flex-wrap gap-3 mt-4">
              <router-link to="/scores" class="btn bg-gold btn-lg px-4 py-3">
                <i class="bi bi-collection-play me-2"></i>Explore Scores
              </router-link>
              <router-link to="/composers" class="btn btn-outline-gold btn-lg px-4 py-3">
                <i class="bi bi-people me-2"></i>Meet the Composers
              </router-link>
            </div>
          </div>
          <div class="col-xl-5">
            <div class="hero-stats">
              <div class="row g-3">
                <div class="col-sm-6">
                  <div class="stat-item">
                    <h3>1000+</h3>
                    <small>Sheet music selections</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="stat-item">
                    <h3>500+</h3>
                    <small>Composers represented</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="stat-item">
                    <h3>50k+</h3>
                    <small>Musicians inspired</small>
                  </div>
                </div>
                <div class="col-sm-6">
                  <div class="stat-item">
                    <h3>4.9/5</h3>
                    <small>Average collector rating</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup></script>

<style scoped>
.musical-notes {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  pointer-events: none;
}

.note-1,
.note-2,
.note-3 {
  position: absolute;
  font-size: 3rem;
  color: rgba(214, 178, 94, 0.24);
  animation: floatDrift 7s ease-in-out infinite;
  text-shadow: 0 0 18px rgba(214, 178, 94, 0.15);
}

.note-1 {
  top: 20%;
  left: 20%;
  animation-delay: 0s;
}

.note-2 {
  top: 60%;
  right: 20%;
  animation-delay: 2s;
}

.note-3 {
  bottom: 22%;
  left: 52%;
  animation-delay: 4s;
}
</style>
