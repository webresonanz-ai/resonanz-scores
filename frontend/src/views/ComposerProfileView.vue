<template>
  <div class="container page-section py-5 mt-5">
    <section class="section-shell composer-profile-shell">
      <div class="section-heading">
        <div class="section-heading-copy">
          <span class="section-eyebrow">
            <i class="bi bi-person-vcard"></i>
            Composer Profile
          </span>
          <h2 class="section-title text-gold">Shape the public composer story that visitors will see</h2>
          <p class="section-description">
            Update your composer identity, profile image, biography, and featured work in one place.
          </p>
        </div>
      </div>

      <div v-if="!authStore.isAuthenticated" class="surface-card p-4 p-lg-5 text-center">
        <h4 class="text-gold mb-3">Login required</h4>
        <p class="text-muted mb-0">Please sign in first, then come back to manage your composer profile.</p>
      </div>

      <div v-else-if="!authStore.isComposer" class="surface-card p-4 p-lg-5 text-center">
        <h4 class="text-gold mb-3">Composer account required</h4>
        <p class="text-muted mb-0">
          This profile editor is available only for approved composer accounts.
        </p>
      </div>

      <div v-else class="row g-4">
        <div class="col-xl-7">
          <div class="surface-card p-4 p-lg-5 h-100">
            <span class="section-eyebrow mb-3">
              <i class="bi bi-pencil-square"></i>
              Public Details
            </span>
            <h4 class="text-gold auth-title mb-4">Edit composer profile</h4>

            <div v-if="profileStore.loading" class="text-muted">Loading composer profile...</div>

            <form v-else @submit.prevent="submitProfile">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label text-muted">Name</label>
                  <input v-model.trim="form.name" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Period</label>
                  <input v-model.trim="form.period" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Nationality</label>
                  <input v-model.trim="form.nationality" type="text" class="form-control" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label text-muted">Featured Work</label>
                  <input v-model.trim="form.featured_work" type="text" class="form-control" required />
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Composer Profile Image</label>
                  <input v-model.trim="form.image" type="url" class="form-control" required />
                </div>

                <div class="col-12">
                  <label class="form-label text-muted">Biography</label>
                  <textarea
                    v-model.trim="form.biography"
                    class="form-control"
                    rows="7"
                    required
                  ></textarea>
                </div>
              </div>

              <p v-if="profileStore.error" class="text-danger small mt-3 mb-0">{{ profileStore.error }}</p>
              <p v-if="successMessage" class="text-success small mt-3 mb-0">{{ successMessage }}</p>

              <button class="btn btn-outline-gold w-100 mt-4" :disabled="profileStore.saving">
                {{ profileStore.saving ? "Saving profile..." : "Save composer profile" }}
              </button>
            </form>
          </div>
        </div>

        <div class="col-xl-5">
          <div class="surface-card p-4 p-lg-5 h-100">
            <span class="section-eyebrow mb-3">
              <i class="bi bi-eye"></i>
              Public Preview
            </span>
            <div class="profile-preview text-center">
              <img :src="previewImage" :alt="form.name || 'Composer profile'" class="rounded-circle preview-avatar mb-3" />
              <span class="difficulty-badge mb-3 d-inline-flex">{{ form.period || "Composer period" }}</span>
              <h4 class="text-gold mb-2">{{ form.name || "Composer name" }}</h4>
              <p class="text-muted mb-3">
                <i class="bi bi-geo-alt me-1"></i>{{ form.nationality || "Nationality" }}
              </p>
              <p class="text-muted preview-biography mb-3">{{ form.biography || "Biography preview will appear here." }}</p>
              <div class="preview-accent">
                <span class="small text-uppercase text-gold d-block mb-1">Featured Work</span>
                <span class="text-muted">{{ form.featured_work || "Featured work title" }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useAuthStore } from "../stores/authStore";
import { useComposerProfileStore } from "../stores/composerProfileStore";

const authStore = useAuthStore();
const profileStore = useComposerProfileStore();

const successMessage = ref("");
const fallbackImage = "https://picsum.photos/400/300?random=200";

const form = reactive({
  name: "",
  period: "",
  nationality: "",
  image: "",
  biography: "",
  featured_work: "",
});

const previewImage = computed(() => form.image || fallbackImage);

onMounted(() => {
  if (authStore.isComposer) {
    loadProfile();
  }
});

watch(
  () => authStore.isComposer,
  (isComposer) => {
    if (isComposer) {
      loadProfile();
      return;
    }

    profileStore.reset();
    resetForm();
  },
);

function resetForm() {
  form.name = "";
  form.period = "";
  form.nationality = "";
  form.image = "";
  form.biography = "";
  form.featured_work = "";
  successMessage.value = "";
}

function fillForm(profile) {
  form.name = profile?.name || "";
  form.period = profile?.period || "";
  form.nationality = profile?.nationality || "";
  form.image = profile?.image || "";
  form.biography = profile?.biography || "";
  form.featured_work = profile?.featuredWork || "";
}

async function loadProfile() {
  successMessage.value = "";

  try {
    const profile = await profileStore.fetchProfile(authStore.token);
    fillForm(profile);
  } catch (error) {
    return error;
  }
}

async function submitProfile() {
  successMessage.value = "";

  try {
    await profileStore.saveProfile(authStore.token, { ...form });
    await authStore.fetchProfile();
    successMessage.value = "Composer profile saved successfully.";
  } catch (error) {
    return error;
  }
}
</script>

<style scoped>
.composer-profile-shell {
  overflow: visible;
}

.auth-title {
  font-family: "Cormorant Garamond", "Times New Roman", serif;
  font-size: 2.1rem;
}

.profile-preview {
  padding: 1rem;
  border-radius: 24px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.04), rgba(214, 178, 94, 0.08));
  border: 1px solid rgba(214, 178, 94, 0.16);
}

.preview-avatar {
  display: block;
  width: 180px;
  height: 180px;
  margin-inline: auto;
  object-fit: cover;
  border: 3px solid rgba(214, 178, 94, 0.45);
  box-shadow: 0 18px 32px rgba(0, 0, 0, 0.24);
}

.preview-biography {
  min-height: 8rem;
}

.preview-accent {
  padding-top: 1rem;
  border-top: 1px solid rgba(214, 178, 94, 0.18);
}
</style>
