<template>
  <section class="hero-section">
    <div class="container text-center">
      <div class="musical-notes">
        <i class="bi bi-music-note note-1"></i>
        <i class="bi bi-music-note-beamed note-2"></i>
        <i class="bi bi-music-note note-3"></i>
      </div>
      <h1 class="hero-title mb-3">The Resonanz</h1>
      <p class="hero-subtitle mb-4">Where Masterpieces Come Alive</p>
      <p class="lead mb-5" style="color: #aaa">
        Discover premium sheet music from the world's greatest composers
      </p>
      <router-link to="/scores" class="btn bg-gold btn-lg px-5 py-3">
        <i class="bi bi-collection-play me-2"></i>Explore Scores
      </router-link>
      <div class="mt-5">
        <div class="row justify-content-center">
          <div class="col-md-3 mb-3">
            <div class="stat-item">
              <h3 class="text-gold">1000+</h3>
              <small class="text-muted">Sheet Music</small>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="stat-item">
              <h3 class="text-gold">500+</h3>
              <small class="text-muted">Composers</small>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="stat-item">
              <h3 class="text-gold">50k+</h3>
              <small class="text-muted">Musicians</small>
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
  color: rgba(201, 168, 76, 0.2);
  animation: float 6s ease-in-out infinite;
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
  bottom: 30%;
  left: 50%;
  animation-delay: 4s;
}

@keyframes float {
  0%,
  100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-20px) rotate(10deg);
  }
}

.stat-item {
  padding: 1rem;
  border: 1px solid rgba(201, 168, 76, 0.3);
  border-radius: 10px;
  background: rgba(201, 168, 76, 0.05);
}
</style>
