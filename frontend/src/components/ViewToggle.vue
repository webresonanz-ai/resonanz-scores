<template>
  <div class="view-toggle">
    <button
      class="btn"
      :class="{ active: scoreStore.viewMode === 'grid' }"
      @click="scoreStore.viewMode = 'grid'"
      aria-label="Grid view"
    >
      <i class="bi bi-grid-3x3-gap-fill"></i>
    </button>
    <button
      class="btn"
      :class="{ active: scoreStore.viewMode === 'list' }"
      @click="scoreStore.viewMode = 'list'"
      aria-label="List view"
    >
      <i class="bi bi-list-ul"></i>
    </button>
  </div>
</template>

<script setup>
import { useScoreStore } from "../stores/scoreStore";

const scoreStore = useScoreStore();
</script>
