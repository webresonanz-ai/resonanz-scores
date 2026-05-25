<template>
  <div id="app">
    <NavbarComponent />
    <router-view />
    <FooterComponent />
  </div>
</template>

<script setup>
import NavbarComponent from "./components/NavbarComponent.vue";
import FooterComponent from "./components/FooterComponent.vue";
</script>
