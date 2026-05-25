<template>
  <div class="row">
    <div v-for="score in featuredScores" :key="score.id" class="col-md-4 mb-4">
      <ScoreCard :score="score" />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useScoreStore } from "../stores/scoreStore";
import ScoreCard from "./ScoreCard.vue";

const scoreStore = useScoreStore();
const featuredScores = computed(() => scoreStore.scores.slice(0, 3));
</script>
