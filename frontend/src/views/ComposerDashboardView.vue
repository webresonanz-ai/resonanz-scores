<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell composer-dashboard">
      <div class="section-heading">
        <div class="section-heading-copy">
          <span class="section-eyebrow">
            <i class="bi bi-journal-richtext"></i>
            Composer Workspace
          </span>
          <h2 class="section-title text-gold">Publish a new composition from one focused dashboard</h2>
          <p class="section-description">
            Upload the score PDF, optional display image, and all of the storefront details in one
            place. The page count is calculated automatically from the uploaded PDF.
          </p>
        </div>
        <div class="dashboard-stat">
          <strong>{{ scoreStore.myScores.length }}</strong>
          <span>saved compositions</span>
        </div>
      </div>

      <div v-if="!authStore.isAuthenticated" class="surface-card p-4 p-lg-5 text-center">
        <h4 class="text-gold mb-3">Login required</h4>
        <p class="text-muted mb-0">
          Please sign in first, then come back here to upload your compositions.
        </p>
      </div>

      <div v-else-if="!authStore.isComposer" class="surface-card p-4 p-lg-5 text-center">
        <h4 class="text-gold mb-3">Composer account required</h4>
        <p class="text-muted mb-0">
          This dashboard is available for approved composer accounts only.
        </p>
      </div>

      <div v-else class="row g-4">
        <div class="col-xl-5">
          <div class="surface-card p-4 p-lg-5 h-100">
            <span class="section-eyebrow mb-3">
              <i class="bi bi-plus-circle"></i>
              New Composition
            </span>
            <h4 class="text-gold auth-title mb-4">Create and publish a score</h4>

            <form @submit.prevent="submitComposition">
              <div class="row g-3">
                <div class="col-12">
                  <label class="form-label text-muted">Title</label>
                  <input v-model.trim="form.title" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Composer</label>
                  <input v-model.trim="form.composer" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted d-flex align-items-center gap-2">
                    <input v-model="form.isArranged" class="form-check-input mt-0" type="checkbox" />
                    Arranged version
                  </label>
                  <input
                    v-model.trim="form.arranger"
                    type="text"
                    class="form-control mt-2"
                    :disabled="!form.isArranged"
                    :required="form.isArranged"
                    placeholder="Arranger name"
                  />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Genre</label>
                  <input v-model.trim="form.genre" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Difficulty</label>
                  <select v-model="form.difficulty" class="form-control" required>
                    <option disabled value="">Select difficulty</option>
                    <option>Beginner</option>
                    <option>Intermediate</option>
                    <option>Advanced</option>
                    <option>Professional</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Price</label>
                  <input
                    v-model.number="form.price"
                    type="number"
                    min="0"
                    step="0.01"
                    class="form-control"
                    required
                  />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Pages</label>
                  <input :value="pageStatus" type="text" class="form-control" readonly />
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Display image (optional)</label>
                  <input
                    class="form-control"
                    type="file"
                    accept="image/png,image/jpeg,image/webp"
                    @change="handleImageChange"
                  />
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Upload PDF</label>
                  <input
                    class="form-control"
                    type="file"
                    accept="application/pdf,.pdf"
                    required
                    @change="handlePdfChange"
                  />
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Description</label>
                  <textarea
                    v-model.trim="form.description"
                    class="form-control"
                    rows="4"
                    required
                  ></textarea>
                </div>
              </div>

              <p v-if="scoreStore.error" class="text-danger small mt-3 mb-0">{{ scoreStore.error }}</p>
              <p v-if="state.successMessage" class="text-success small mt-3 mb-0">{{ state.successMessage }}</p>

              <button class="btn btn-outline-gold w-100 mt-4" :disabled="scoreStore.submitting">
                {{ scoreStore.submitting ? "Saving composition..." : "Create composition" }}
              </button>
            </form>
          </div>
        </div>

        <div class="col-xl-7">
          <div class="surface-card p-4 p-lg-5 h-100">
            <span class="section-eyebrow mb-3">
              <i class="bi bi-collection-play"></i>
              Preview and Library
            </span>
            <div class="preview-panel mb-4">
              <img :src="coverPreview" alt="Composition cover preview" class="preview-cover" />
              <div>
                <p class="preview-label mb-2">Display cover preview</p>
                <h4 class="text-gold mb-2">{{ form.title || "Untitled composition" }}</h4>
                <p class="text-muted mb-2">
                  {{ form.composer || authStore.user?.name || "Composer name" }}
                  <span v-if="form.isArranged && form.arranger"> · Arranged by {{ form.arranger }}</span>
                </p>
                <p class="text-muted mb-0 small">
                  PDF file: {{ form.pdfFile?.name || "Waiting for upload" }}
                </p>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
              <h4 class="text-gold auth-title mb-0">My compositions</h4>
              <span class="text-muted small">{{ scoreStore.myScores.length }} items</span>
            </div>

            <div v-if="scoreStore.loading" class="text-muted">Loading your compositions...</div>

            <div v-else-if="scoreStore.myScores.length" class="row g-3">
              <div
                v-for="score in scoreStore.myScores"
                :key="score.id"
                class="col-lg-6"
              >
                <article class="detail-card composition-card h-100">
                  <img :src="score.image" :alt="score.title" class="composition-image" />
                  <div class="p-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                      <h5 class="text-gold mb-0">{{ score.title }}</h5>
                      <span class="price-tag">${{ formatPrice(score.price) }}</span>
                    </div>
                    <p class="text-muted small mb-2">
                      {{ score.composer }}
                      <span v-if="score.is_arranged && score.arranger"> · Arranged by {{ score.arranger }}</span>
                    </p>
                    <div class="composition-meta mb-3">
                      <span class="difficulty-badge">{{ score.genre }}</span>
                      <span class="difficulty-badge">{{ score.difficulty }}</span>
                      <span class="difficulty-badge">{{ score.pages }} pages</span>
                    </div>
                    <p class="text-muted small mb-0">{{ score.description }}</p>
                  </div>
                </article>
              </div>
            </div>

            <p v-else class="text-muted mb-0">
              Your uploaded compositions will appear here after you create the first one.
            </p>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, reactive, watch } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useScoreStore } from "../stores/scoreStore";

const authStore = useAuthStore();
const scoreStore = useScoreStore();

const DEFAULT_COVER = "/default-score-cover.svg";

const form = reactive({
  title: "",
  composer: "",
  arranger: "",
  genre: "",
  difficulty: "",
  price: 0,
  description: "",
  isArranged: false,
  imageFile: null,
  pdfFile: null,
});

const pageStatus = computed(() => {
  if (!form.pdfFile) {
    return "Upload a PDF to count pages";
  }

  if (state.pdfPageCount > 0) {
    return `${state.pdfPageCount} pages detected`;
  }

  return state.pdfStatusMessage || "Counting pages...";
});

const coverPreview = computed(() => state.imagePreview || DEFAULT_COVER);

const state = reactive({
  imagePreview: "",
  successMessage: "",
  pdfPageCount: 0,
  pdfStatusMessage: "",
});
let imageObjectUrl = "";

onMounted(async () => {
  if (authStore.isComposer) {
    await scoreStore.fetchMyScores(authStore.token);
  }
});

watch(
  () => authStore.user?.name,
  (name) => {
    if (!form.composer && name) {
      form.composer = name;
    }
  },
  { immediate: true },
);

watch(
  () => authStore.isComposer,
  async (isComposer) => {
    if (isComposer) {
      await scoreStore.fetchMyScores(authStore.token);
    }
  },
);

watch(
  () => form.isArranged,
  (isArranged) => {
    if (!isArranged) {
      form.arranger = "";
    }
  },
);

function handleImageChange(event) {
  const [file] = event.target.files || [];
  form.imageFile = file || null;

  if (imageObjectUrl) {
    URL.revokeObjectURL(imageObjectUrl);
    imageObjectUrl = "";
  }

  if (file) {
    imageObjectUrl = URL.createObjectURL(file);
    state.imagePreview = imageObjectUrl;
    return;
  }

  state.imagePreview = "";
}

async function handlePdfChange(event) {
  const [file] = event.target.files || [];
  form.pdfFile = file || null;
  state.pdfPageCount = 0;
  state.pdfStatusMessage = "";

  if (!file) {
    return;
  }

  state.pdfStatusMessage = "Counting pages...";

  try {
    state.pdfPageCount = await countPdfPages(file);
    state.pdfStatusMessage = state.pdfPageCount > 0 ? "" : "Page count unavailable";
  } catch (error) {
    state.pdfStatusMessage = "Unable to count pages from this PDF";
    return error;
  }
}

function resetForm() {
  form.title = "";
  form.composer = authStore.user?.name || "";
  form.arranger = "";
  form.genre = "";
  form.difficulty = "";
  form.price = 0;
  form.description = "";
  form.isArranged = false;
  form.imageFile = null;
  form.pdfFile = null;
  state.imagePreview = "";
  state.pdfPageCount = 0;
  state.pdfStatusMessage = "";

  if (imageObjectUrl) {
    URL.revokeObjectURL(imageObjectUrl);
    imageObjectUrl = "";
  }
}

async function countPdfPages(file) {
  const buffer = await file.arrayBuffer();
  const content = new TextDecoder("latin1").decode(buffer);
  const pageMatches = content.match(/\/Type\s*\/Page\b/g);

  if (pageMatches?.length) {
    return pageMatches.length;
  }

  const countMatches = [...content.matchAll(/\/Count\s+(\d+)/g)]
    .map((match) => Number.parseInt(match[1], 10))
    .filter((value) => Number.isFinite(value) && value > 0);

  if (countMatches.length) {
    return Math.max(...countMatches);
  }

  throw new Error("Unable to detect PDF page count.");
}

function formatPrice(value) {
  return Number(value || 0).toFixed(2);
}

async function submitComposition() {
  state.successMessage = "";

  const payload = new FormData();
  payload.append("title", form.title);
  payload.append("composer", form.composer);
  payload.append("arranger", form.arranger);
  payload.append("is_arranged", String(form.isArranged));
  payload.append("genre", form.genre);
  payload.append("difficulty", form.difficulty);
  payload.append("price", String(form.price));
  payload.append("description", form.description);

  if (form.imageFile) {
    payload.append("image", form.imageFile);
  }

  if (form.pdfFile) {
    payload.append("upload_pdf", form.pdfFile);
  }

  try {
    const created = await scoreStore.createScore(authStore.token, payload);
    state.successMessage = `Saved "${created.title}" with ${created.pages} pages.`;
    resetForm();
  } catch (error) {
    return error;
  }
}

onBeforeUnmount(() => {
  if (imageObjectUrl) {
    URL.revokeObjectURL(imageObjectUrl);
  }
});
</script>

<style scoped>
.composer-dashboard {
  overflow: visible;
}

.dashboard-stat {
  min-width: 10rem;
  padding: 1rem 1.25rem;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.24);
  background: linear-gradient(135deg, rgba(214, 178, 94, 0.12), rgba(70, 113, 170, 0.14));
  text-align: center;
}

.dashboard-stat strong {
  display: block;
  font-size: 2rem;
  color: var(--gold-soft);
}

.dashboard-stat span {
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.74rem;
}

.auth-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2.1rem;
}

.preview-panel {
  display: grid;
  grid-template-columns: 168px 1fr;
  gap: 1.25rem;
  align-items: center;
  padding: 1rem;
  border-radius: 24px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04), rgba(214, 178, 94, 0.08));
  border: 1px solid rgba(214, 178, 94, 0.16);
}

.preview-cover,
.composition-image {
  width: 100%;
  aspect-ratio: 4 / 5;
  object-fit: cover;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.18);
}

.preview-label {
  color: var(--gold-soft);
  text-transform: uppercase;
  letter-spacing: 0.12em;
  font-size: 0.74rem;
}

.composition-card {
  overflow: hidden;
}

.composition-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
}

@media (max-width: 767.98px) {
  .preview-panel {
    grid-template-columns: 1fr;
  }
}
</style>
