<template>
  <div class="row">
    <div v-for="composer in topComposers" :key="composer.id" class="col-xl-2 col-lg-4 col-md-4 col-6 mb-4">
      <ComposerCard :composer="composer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useComposerStore } from "../stores/composerStore";
import ComposerCard from "./ComposerCard.vue";

const composerStore = useComposerStore();
const topComposers = computed(() => composerStore.composers.slice(0, 6));
</script>
