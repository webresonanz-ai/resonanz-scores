<template>
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
      <router-link class="navbar-brand d-flex align-items-center gap-2" to="/">
        <span class="brand-mark">
          <i class="bi bi-music-note-beamed"></i>
        </span>
        <span>The Resonanz</span>
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
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              :class="{ active: isScoresRoute }"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-file-earmark-music me-1"></i> Scores
            </a>
            <ul class="dropdown-menu dropdown-menu-end nav-dropdown">
              <li>
                <router-link class="dropdown-item" to="/scores">
                  Browse catalog
                </router-link>
              </li>
              <li v-if="authStore.isStaff">
                <router-link class="dropdown-item" to="/admin/composition-approval">
                  Approve compositions
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              :class="{ active: isComposersRoute }"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-people me-1"></i> Composers
            </a>
            <ul class="dropdown-menu dropdown-menu-end nav-dropdown">
              <li>
                <router-link class="dropdown-item" to="/composers">
                  Browse composers
                </router-link>
              </li>
              <li v-if="authStore.isStaff">
                <router-link class="dropdown-item" to="/admin/composer-verification">
                  Verify composers
                </router-link>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <router-link class="nav-link" to="/profile" active-class="active">
              <i class="bi bi-person-circle me-1"></i>
              {{ authStore.isAuthenticated ? "My Profile" : "Login" }}
            </router-link>
          </li>
          <li v-if="authStore.isComposer" class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              :class="{ active: isComposerRoute }"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              <i class="bi bi-journal-music me-1"></i> Composer Dashboard
            </a>
            <ul class="dropdown-menu dropdown-menu-end nav-dropdown">
              <li>
                <router-link class="dropdown-item" to="/composer/profile">
                  Composer Profile
                </router-link>
              </li>
              <li>
                <router-link class="dropdown-item" to="/composer/dashboard">
                  Composer Workspace
                </router-link>
              </li>
            </ul>
          </li>
          <li v-if="authStore.isAuthenticated" class="nav-item">
            <button class="btn btn-link nav-link" type="button" @click="authStore.logout()">
              <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
          </li>
          <li class="nav-item ms-lg-3">
            <router-link class="btn btn-outline-gold btn-sm nav-cta" to="/cart">
              <i class="bi bi-cart3 me-1"></i> Cart ({{ cartStore.itemCount }})
            </router-link>
          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "../stores/authStore";
import { useCartStore } from "../stores/cartStore";

const authStore = useAuthStore();
const cartStore = useCartStore();
const route = useRoute();

const isComposerRoute = computed(() => route.path.startsWith("/composer/"));
const isScoresRoute = computed(
  () => route.path === "/scores" || route.path.startsWith("/scores/") || route.path === "/admin/composition-approval",
);
const isComposersRoute = computed(
  () => route.path === "/composers" || route.path === "/admin/composer-verification",
);
</script>

<style scoped>
.brand-mark {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 999px;
  background: rgba(214, 178, 94, 0.12);
  border: 1px solid rgba(214, 178, 94, 0.18);
  color: var(--gold-soft);
  font-size: 1rem;
}

.nav-link {
  position: relative;
  color: var(--text-soft) !important;
  font-weight: 600;
  transition: all 0.3s ease;
  margin: 0 0.5rem;
}

.nav-link:hover,
.nav-link.active {
  color: var(--gold-soft) !important;
}

.nav-link::after {
  content: "";
  position: absolute;
  bottom: -0.2rem;
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

.nav-cta {
  padding-inline: 1rem;
}

.nav-dropdown {
  min-width: 14rem;
  border-radius: 18px;
  border: 1px solid rgba(214, 178, 94, 0.2);
  background: rgba(10, 15, 24, 0.96);
  box-shadow: 0 18px 40px rgba(0, 0, 0, 0.28);
}

.dropdown-item {
  color: var(--text-soft);
  font-weight: 600;
  padding: 0.8rem 1rem;
}

.dropdown-item:hover,
.dropdown-item.router-link-active {
  color: var(--gold-soft);
  background: rgba(214, 178, 94, 0.08);
}

@media (max-width: 991.98px) {
  .nav-link {
    margin: 0.35rem 0;
  }
}
</style>
