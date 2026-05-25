<template>
  <footer class="footer py-5 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <h4 class="text-gold mb-3">
            <i class="bi bi-music-note-beamed me-2"></i>
            The Resonanz
          </h4>
          <p class="text-muted">
            Premium sheet music for discerning musicians. Discover masterpieces from the world's
            greatest composers.
          </p>
          <div class="social-links mt-3">
            <a href="#" class="text-gold me-3"><i class="bi bi-facebook fs-5"></i></a>
            <a href="#" class="text-gold me-3"><i class="bi bi-instagram fs-5"></i></a>
            <a href="#" class="text-gold me-3"><i class="bi bi-twitter fs-5"></i></a>
            <a href="#" class="text-gold"><i class="bi bi-youtube fs-5"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-4">
          <h5 class="text-gold mb-3">Quick Links</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><router-link to="/" class="footer-link">Home</router-link></li>
            <li class="mb-2"><router-link to="/scores" class="footer-link">Scores</router-link></li>
            <li class="mb-2">
              <router-link to="/composers" class="footer-link">Composers</router-link>
            </li>
            <li class="mb-2">
              <router-link to="/profile" class="footer-link">Profile</router-link>
            </li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4 mb-4">
          <h5 class="text-gold mb-3">Categories</h5>
          <ul class="list-unstyled">
            <li class="mb-2"><a href="#" class="footer-link">Classical</a></li>
            <li class="mb-2"><a href="#" class="footer-link">Romantic</a></li>
            <li class="mb-2"><a href="#" class="footer-link">Baroque</a></li>
            <li class="mb-2"><a href="#" class="footer-link">Contemporary</a></li>
          </ul>
        </div>
        <div class="col-lg-3 col-md-4 mb-4">
          <h5 class="text-gold mb-3">Newsletter</h5>
          <p class="text-muted">Subscribe for updates on new scores and exclusive offers.</p>
          <div class="input-group mb-3">
            <input
              type="email"
              class="form-control"
              placeholder="Your email"
              aria-label="Your email"
            />
            <button class="btn bg-gold" type="button">
              <i class="bi bi-send"></i>
            </button>
          </div>
        </div>
      </div>
      <hr class="border-gold my-4" />
      <div class="text-center text-muted">
        <p class="mb-0">
          &copy; 2024 The Resonanz. All rights reserved. Crafted with
          <i class="bi bi-heart-fill text-danger"></i> for music lovers.
        </p>
      </div>
    </div>
  </footer>
</template>

<script setup></script>

<style scoped>
.footer-link {
  color: #999;
  text-decoration: none;
  transition: all 0.3s ease;
}

.footer-link:hover {
  color: var(--gold);
  padding-left: 5px;
}

.form-control {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid var(--gold);
  color: var(--light);
}

.form-control:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: var(--gold);
  box-shadow: 0 0 0 0.2rem rgba(201, 168, 76, 0.25);
  color: var(--light);
}
</style>
