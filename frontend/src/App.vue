<template>
  <div id="app">
    <NavbarComponent />
    <router-view />
    <FooterComponent />
  </div>
</template>

<script setup>
import { onMounted } from "vue";
import NavbarComponent from "./components/NavbarComponent.vue";
import FooterComponent from "./components/FooterComponent.vue";
import { useAuthStore } from "./stores/authStore";
import { useComposerStore } from "./stores/composerStore";
import { useScoreStore } from "./stores/scoreStore";

const authStore = useAuthStore();
const composerStore = useComposerStore();
const scoreStore = useScoreStore();

onMounted(() => {
  scoreStore.fetchScores();
  composerStore.fetchComposers();
  authStore.fetchProfile();
});
</script>
