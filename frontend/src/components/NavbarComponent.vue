<template>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <router-link class="navbar-brand" to="/">
        <i class="bi bi-music-note-beamed me-2"></i>
        The Resonanz
      </router-link>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item">
            <router-link class="nav-link" to="/" exact-active-class="active">
              <i class="bi bi-house-door me-1"></i> Home
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/scores" active-class="active">
              <i class="bi bi-file-earmark-music me-1"></i> Scores
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/composers" active-class="active">
              <i class="bi bi-people me-1"></i> Composers
            </router-link>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/profile" active-class="active">
              <i class="bi bi-person-circle me-1"></i>
              {{ authStore.isAuthenticated ? "My Profile" : "Login" }}
            </router-link>
          </li>
          <li v-if="authStore.isAuthenticated" class="nav-item">
            <button class="btn btn-link nav-link" type="button" @click="authStore.logout()">
              <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
          </li>
          <li class="nav-item ms-lg-3">
            <button class="btn btn-outline-gold btn-sm">
              <i class="bi bi-cart3 me-1"></i> Cart (0)
            </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from "../stores/authStore";

const authStore = useAuthStore();
</script>

<style scoped>
.nav-link {
  position: relative;
  color: var(--light) !important;
  transition: all 0.3s ease;
  margin: 0 0.5rem;
}

.nav-link:hover,
.nav-link.active {
  color: var(--gold) !important;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background: var(--gradient-gold);
  transition: width 0.3s ease;
}

.nav-link:hover::after,
.nav-link.active::after {
  width: 100%;
}
</style>
