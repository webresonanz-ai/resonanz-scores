import { defineStore } from "pinia";
import { ref } from "vue";

export const useScoreStore = defineStore("scores", () => {
  const scores = ref([
    {
      id: 1,
      title: "Moonlight Sonata",
      composer: "Ludwig van Beethoven",
      genre: "Classical",
      difficulty: "Advanced",
      price: 19.99,
      image: "https://picsum.photos/400/300?random=1",
      description: "Complete piano sonata No. 14 in C-sharp minor",
      pages: 23,
      rating: 4.9,
    },
    {
      id: 2,
      title: "Clair de Lune",
      composer: "Claude Debussy",
      genre: "Impressionist",
      difficulty: "Intermediate",
      price: 14.99,
      image: "https://picsum.photos/400/300?random=2",
      description: "From Suite Bergamasque, one of the most beloved piano pieces",
      pages: 15,
      rating: 4.8,
    },
    {
      id: 3,
      title: "Nocturne in E-flat Major",
      composer: "Frédéric Chopin",
      genre: "Romantic",
      difficulty: "Advanced",
      price: 17.99,
      image: "https://picsum.photos/400/300?random=3",
      description: "Op. 9 No. 2, one of Chopin's most famous nocturnes",
      pages: 12,
      rating: 4.9,
    },
    {
      id: 4,
      title: "The Entertainer",
      composer: "Scott Joplin",
      genre: "Ragtime",
      difficulty: "Intermediate",
      price: 12.99,
      image: "https://picsum.photos/400/300?random=4",
      description: "Classic ragtime piece, perfect for intermediate pianists",
      pages: 8,
      rating: 4.7,
    },
    {
      id: 5,
      title: "Canon in D",
      composer: "Johann Pachelbel",
      genre: "Baroque",
      difficulty: "Beginner",
      price: 9.99,
      image: "https://picsum.photos/400/300?random=5",
      description: "Beautiful and accessible arrangement for piano",
      pages: 6,
      rating: 4.6,
    },
    {
      id: 6,
      title: "Rhapsody in Blue",
      composer: "George Gershwin",
      genre: "Jazz/Classical",
      difficulty: "Advanced",
      price: 24.99,
      image: "https://picsum.photos/400/300?random=6",
      description: "Iconic fusion of classical music with jazz elements",
      pages: 35,
      rating: 4.9,
    },
  ]);

  const viewMode = ref("grid");

  function toggleView() {
    viewMode.value = viewMode.value === "grid" ? "list" : "grid";
  }

  return { scores, viewMode, toggleView };
});
