<template>
  <footer class="footer py-5 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <h4 class="text-gold mb-3">
            <i class="bi bi-music-note-beamed me-2"></i>
            The Resonanz
          </h4>
          <p class="text-muted footer-copy">
            Premium sheet music for discerning musicians. Discover masterpieces from the world's
            greatest composers.
          </p>
          <div class="social-links mt-3">
            <a href="#" class="social-link me-2"><i class="bi bi-facebook fs-5"></i></a>
            <a href="#" class="social-link me-2"><i class="bi bi-instagram fs-5"></i></a>
            <a href="#" class="social-link me-2"><i class="bi bi-twitter fs-5"></i></a>
            <a href="#" class="social-link"><i class="bi bi-youtube fs-5"></i></a>
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
          <p class="text-muted footer-copy">Subscribe for updates on new scores and exclusive offers.</p>
          <div class="input-group mb-3">
            <input
              type="email"
              class="form-control glass-input"
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
  color: var(--text-muted);
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.footer-link:hover {
  color: var(--gold-soft);
  padding-left: 5px;
}

.footer-copy {
  max-width: 22rem;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 999px;
  color: var(--gold-soft);
  background: rgba(214, 178, 94, 0.08);
  border: 1px solid rgba(214, 178, 94, 0.16);
  transition:
    transform 0.25s ease,
    background 0.25s ease,
    border-color 0.25s ease;
}

.social-link:hover {
  transform: translateY(-3px);
  background: rgba(214, 178, 94, 0.18);
  border-color: rgba(214, 178, 94, 0.34);
}
</style>
