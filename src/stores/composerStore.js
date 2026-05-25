import { defineStore } from "pinia";
import { ref } from "vue";

export const useComposerStore = defineStore("composers", () => {
  const composers = ref([
    {
      id: 1,
      name: "Ludwig van Beethoven",
      period: "Classical/Romantic",
      nationality: "German",
      image: "https://picsum.photos/400/300?random=10",
      works: 138,
      biography: "One of the most influential composers in Western classical music",
      featuredWork: "Symphony No. 9",
    },
    {
      id: 2,
      name: "Frédéric Chopin",
      period: "Romantic",
      nationality: "Polish",
      image: "https://picsum.photos/400/300?random=11",
      works: 230,
      biography: "Poet of the piano, revolutionized the art of piano composition",
      featuredWork: "Nocturnes",
    },
    {
      id: 3,
      name: "Claude Debussy",
      period: "Impressionist",
      nationality: "French",
      image: "https://picsum.photos/400/300?random=12",
      works: 141,
      biography: "Pioneer of Impressionist music, created unique harmonic languages",
      featuredWork: "Prélude à l'après-midi d'un faune",
    },
    {
      id: 4,
      name: "Johann Sebastian Bach",
      period: "Baroque",
      nationality: "German",
      image: "https://picsum.photos/400/300?random=13",
      works: 1128,
      biography: "Master of counterpoint and harmonic organization",
      featuredWork: "Brandenburg Concertos",
    },
    {
      id: 5,
      name: "Wolfgang Amadeus Mozart",
      period: "Classical",
      nationality: "Austrian",
      image: "https://picsum.photos/400/300?random=14",
      works: 626,
      biography: "Prolific and influential composer of the Classical era",
      featuredWork: "The Magic Flute",
    },
    {
      id: 6,
      name: "Pyotr Ilyich Tchaikovsky",
      period: "Romantic",
      nationality: "Russian",
      image: "https://picsum.photos/400/300?random=15",
      works: 169,
      biography: "First Russian composer to gain international recognition",
      featuredWork: "Swan Lake",
    },
  ]);

  return { composers };
});
