import { createRouter, createWebHistory } from "vue-router";
import HomeView from "../views/HomeView.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
    },
    {
      path: "/scores",
      name: "scores",
      component: () => import("../views/ScoresView.vue"),
    },
    {
      path: "/composers",
      name: "composers",
      component: () => import("../views/ComposersView.vue"),
    },
    {
      path: "/profile",
      name: "profile",
      component: () => import("../views/ProfileView.vue"),
    },
    {
      path: "/composer/dashboard",
      name: "composer-dashboard",
      component: () => import("../views/ComposerDashboardView.vue"),
    },
    {
      path: "/admin/composer-verification",
      name: "composer-verification",
      component: () => import("../views/AdminComposerVerificationView.vue"),
    },
  ],
});

export default router;
