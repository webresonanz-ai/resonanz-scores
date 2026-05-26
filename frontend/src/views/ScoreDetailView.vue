<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell">
      <div class="mb-4">
        <RouterLink :to="{ name: 'scores' }" class="back-link">
          <i class="bi bi-arrow-left"></i>
          Back to scores
        </RouterLink>
      </div>

      <div v-if="scoreStore.loading" class="empty-state">
        <i class="bi bi-hourglass-split"></i>
        <p class="mb-0">Loading score details...</p>
      </div>

      <div v-else-if="scoreStore.error" class="empty-state">
        <i class="bi bi-exclamation-diamond"></i>
        <p class="mb-0">{{ scoreStore.error }}</p>
      </div>

      <div v-else-if="score" class="row g-4">
        <div class="col-lg-5">
          <div class="surface-card detail-panel p-4 h-100">
            <img :src="score.image" :alt="score.title" class="detail-cover mb-4" />

            <span class="section-eyebrow mb-3">
              <i class="bi bi-file-earmark-music-fill"></i>
              Score detail
            </span>
            <h1 class="section-title text-gold detail-title">{{ score.title }}</h1>
            <p class="detail-lead mb-4">
              {{ score.description }}
            </p>

            <div class="detail-grid mb-4">
              <div class="meta-block">
                <span class="meta-label">Composer</span>
                <strong>{{ score.composer }}</strong>
              </div>
              <div class="meta-block" v-if="score.is_arranged && score.arranger">
                <span class="meta-label">Arranger</span>
                <strong>{{ score.arranger }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Genre</span>
                <strong>{{ score.genre }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Difficulty</span>
                <strong>{{ score.difficulty }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Pages</span>
                <strong>{{ score.pages }}</strong>
              </div>
              <div class="meta-block">
                <span class="meta-label">Rating</span>
                <strong>{{ score.rating }}</strong>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
              <span class="price-tag detail-price">${{ score.price }}</span>
              <button class="btn btn-outline-gold px-4 py-2" type="button" @click="handleAddToCart">
                <i class="bi bi-cart-plus me-2"></i>
                Add to Cart
              </button>
            </div>
            <p v-if="cartStore.successMessage" class="text-success small mb-0 mt-3">
              {{ cartStore.successMessage }}
            </p>
            <p v-if="cartStore.error" class="text-danger small mb-0 mt-2">
              {{ cartStore.error }}
            </p>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="surface-card pdf-panel p-3 p-md-4">
            <div class="pdf-panel-header">
              <div>
                <span class="section-eyebrow mb-2">
                  <i class="bi bi-filetype-pdf"></i>
                  Preview
                </span>
                <h2 class="pdf-title">PDF Preview</h2>
              </div>
              <span class="meta-chip">
                <i class="bi bi-journal-richtext text-gold"></i>
                {{ score.pages }} pages
              </span>
            </div>

            <div
              v-if="score.has_pdf_preview"
              ref="pdfPreviewRef"
              class="pdf-preview-wrap pdf-preview-protected"
              @contextmenu.prevent
              @dragstart.prevent
              @copy.prevent
              @cut.prevent
            >
              <div v-if="pdfLoading" class="pdf-preview-status">
                <i class="bi bi-hourglass-split"></i>
                <p class="mb-0">Loading PDF preview...</p>
              </div>
              <div v-else-if="pdfError" class="pdf-preview-status">
                <i class="bi bi-exclamation-diamond"></i>
                <p class="mb-0">{{ pdfError }}</p>
              </div>
              <div
                v-else
                class="pdf-pages"
                role="document"
                :aria-label="`PDF preview for ${score.title}`"
              >
                <div v-for="page in pdfPages" :key="page.pageNumber" class="pdf-page">
                  <canvas
                    :ref="(element) => mountPdfPageCanvas(element, page)"
                    class="pdf-page-canvas"
                    :width="page.width"
                    :height="page.height"
                    :aria-label="`Page ${page.pageNumber} of ${score.title}`"
                  ></canvas>
                  <div class="pdf-page-blur-shield">
                    <span class="pdf-page-preview-label">For preview only</span>
                  </div>
                  <div class="pdf-page-interaction-shield" aria-hidden="true"></div>
                </div>
              </div>
            </div>

            <div v-else class="empty-state compact">
              <i class="bi bi-file-earmark-x"></i>
              <p class="mb-0">No PDF preview is available for this score yet.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { getDocument, GlobalWorkerOptions } from "pdfjs-dist";
import pdfjsWorker from "pdfjs-dist/build/pdf.worker.mjs?url";
import { fetchPreviewBinary } from "../lib/api";
import { useAuthStore } from "../stores/authStore";
import { useCartStore } from "../stores/cartStore";
import { useScoreStore } from "../stores/scoreStore";

GlobalWorkerOptions.workerSrc = pdfjsWorker;

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const scoreStore = useScoreStore();
const cartStore = useCartStore();

const score = computed(() => scoreStore.currentScore);

const pdfPreviewRef = ref(null);
const pdfLoading = ref(false);
const pdfError = ref("");
const pdfPages = ref([]);

let pdfRenderGeneration = 0;

function clearPdfPages() {
  pdfPages.value = [];
}

function mountPdfPageCanvas(element, page) {
  if (!element || typeof page.paint !== "function") {
    return;
  }

  page.paint(element);
}

function blockPreviewShortcuts(event) {
  if (!pdfPreviewRef.value) {
    return;
  }

  const key = event.key.toLowerCase();

  if ((event.ctrlKey || event.metaKey) && ["s", "p", "c", "u"].includes(key)) {
    event.preventDefault();
  }
}

async function renderPdfPreview(scoreId) {
  const generation = ++pdfRenderGeneration;
  clearPdfPages();
  pdfError.value = "";
  pdfLoading.value = true;

  try {
    const pdfData = await fetchPreviewBinary(`/score-pdf-preview?id=${scoreId}`);
    const loadingTask = getDocument({ data: pdfData });
    const pdf = await loadingTask.promise;

    if (generation !== pdfRenderGeneration) {
      await pdf.destroy();
      return;
    }

    const pages = [];
    const scale = Math.min(window.devicePixelRatio || 1, 2) * 1.35;

    for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber += 1) {
      if (generation !== pdfRenderGeneration) {
        await pdf.destroy();
        return;
      }

      const page = await pdf.getPage(pageNumber);
      const viewport = page.getViewport({ scale });
      const renderCanvas = document.createElement("canvas");
      const context = renderCanvas.getContext("2d");

      if (!context) {
        throw new Error("Canvas is not available.");
      }

      renderCanvas.width = viewport.width;
      renderCanvas.height = viewport.height;

      await page.render({ canvas: renderCanvas, canvasContext: context, viewport }).promise;

      pages.push({
        pageNumber,
        width: renderCanvas.width,
        height: renderCanvas.height,
        paint(target) {
          const targetContext = target.getContext("2d");

          if (!targetContext) {
            return;
          }

          target.width = renderCanvas.width;
          target.height = renderCanvas.height;
          targetContext.drawImage(renderCanvas, 0, 0);
        },
      });
    }

    if (generation !== pdfRenderGeneration) {
      await pdf.destroy();
      return;
    }

    pdfPages.value = pages;
    await pdf.destroy();
  } catch {
    if (generation === pdfRenderGeneration) {
      pdfError.value = "Could not load PDF preview.";
    }
  } finally {
    if (generation === pdfRenderGeneration) {
      pdfLoading.value = false;
    }
  }
}

watch(
  () => [score.value?.has_pdf_preview, Number(route.params.id)],
  ([hasPreview, scoreId]) => {
    pdfRenderGeneration += 1;
    clearPdfPages();
    pdfError.value = "";

    if (!hasPreview || !Number.isFinite(scoreId) || scoreId <= 0) {
      pdfLoading.value = false;
      return;
    }

    renderPdfPreview(scoreId);
  },
  { immediate: true },
);

onBeforeUnmount(() => {
  pdfRenderGeneration += 1;
  clearPdfPages();
  window.removeEventListener("keydown", blockPreviewShortcuts);
});

async function loadScore() {
  const scoreId = Number(route.params.id);

  if (!Number.isFinite(scoreId) || scoreId <= 0) {
    scoreStore.currentScore = null;
    scoreStore.error = "Score not found.";
    return;
  }

  try {
    await scoreStore.fetchScore(scoreId);
  } catch {
    // Store state already captures the fetch error for the UI.
  }
}

onMounted(() => {
  loadScore();
  window.addEventListener("keydown", blockPreviewShortcuts);
});
watch(() => route.params.id, loadScore);

function handleAddToCart() {
  if (!score.value) {
    return;
  }

  const added = cartStore.addToCart(score.value);

  if (!added && !authStore.isAuthenticated) {
    router.push({ name: "profile" });
  }
}
</script>

<style scoped>
.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  color: var(--gold-soft);
  font-weight: 700;
}

.detail-panel,
.pdf-panel {
  height: 100%;
}

.detail-cover {
  width: 100%;
  max-height: 24rem;
  object-fit: cover;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.18);
}

.detail-title {
  font-size: clamp(2.6rem, 5vw, 4rem);
}

.detail-lead {
  color: var(--text-soft);
  font-size: 1.02rem;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

.meta-block {
  padding: 1rem;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(214, 178, 94, 0.14);
}

.meta-label {
  display: block;
  margin-bottom: 0.35rem;
  color: var(--text-muted);
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.detail-price {
  font-size: 2rem;
}

.pdf-panel-header {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 1rem;
  margin-bottom: 1.25rem;
}

.pdf-title {
  margin: 0;
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2rem;
  color: var(--text);
}

.pdf-preview-wrap {
  overflow: hidden;
  border-radius: 20px;
  border: 1px solid rgba(214, 178, 94, 0.16);
  background: rgba(4, 8, 15, 0.65);
  min-height: 24rem;
  max-height: 72vh;
  overflow-y: auto;
}

.pdf-preview-protected {
  user-select: none;
  -webkit-user-select: none;
  -webkit-touch-callout: none;
}

.pdf-preview-status {
  display: grid;
  place-items: center;
  gap: 0.8rem;
  min-height: 24rem;
  padding: 2rem;
  text-align: center;
  color: var(--text-muted);
}

.pdf-preview-status i {
  font-size: 2rem;
  color: var(--gold-soft);
}

.pdf-pages {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.75rem;
}

.pdf-page {
  position: relative;
  overflow: hidden;
  border-radius: 12px;
  background: #fff;
  line-height: 0;
}

.pdf-page-canvas {
  display: block;
  width: 100%;
  height: auto;
  pointer-events: none;
}

.pdf-page-interaction-shield {
  position: absolute;
  inset: 0;
  z-index: 2;
}

.pdf-page-blur-shield {
  position: absolute;
  inset: 25% 0 0;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  pointer-events: none;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
}

.pdf-page-preview-label {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: clamp(1.35rem, 3.5vw, 2rem);
  font-weight: 700;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  text-align: center;
  color: rgba(120, 88, 28, 0.82);
  text-shadow: 0 1px 0 rgba(255, 255, 255, 0.65);
  user-select: none;
}

.empty-state {
  display: grid;
  place-items: center;
  gap: 0.8rem;
  min-height: 18rem;
  text-align: center;
  color: var(--text-muted);
}

.empty-state i {
  font-size: 2rem;
  color: var(--gold-soft);
}

.empty-state.compact {
  min-height: 24rem;
}

@media (max-width: 767.98px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .pdf-panel-header {
    flex-direction: column;
  }

  .pdf-preview-wrap {
    max-height: 60vh;
  }
}
</style>
