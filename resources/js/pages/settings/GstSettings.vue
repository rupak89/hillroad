<template>
  <section class="section main-section">
    <router-link to="/" class="button is-small">
      <span class="icon"><i class="mdi mdi-arrow-left"></i></span>
      <span class="sm:hidden lg:inline">Dashboard</span>
    </router-link>

    <section class="is-hero-bar">
      <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
        <h1>GST Settings</h1>
        <span class="nz-badge">New Zealand</span>
      </div>
    </section>

    <div class="card">
      <header class="card-header">
        <p class="card-header-title">
          <span class="icon"><i class="mdi mdi-percent"></i></span>
          GST Configuration
        </p>
      </header>

      <div class="card-content">
        <div v-if="isLoading" class="text-center py-6">
          <div class="button is-static">Loading GST settings...</div>
        </div>

        <form v-else @submit.prevent="saveSettings">
          <div class="settings-note">
            <p>
              GST is applied to recipe costs and menu selling prices. The New Zealand
              standard rate is <strong>15%</strong>, and prices shown to customers are
              quoted GST-inclusive.
            </p>
          </div>

          <div class="field">
            <label class="label">GST registered</label>
            <div class="control">
              <label class="checkbox">
                <input type="checkbox" v-model="form.gst_registered">
                <span class="check"></span>
                <span class="control-label">This business is registered for GST</span>
              </label>
            </div>
            <p class="help">
              When this is off, no GST is added to costs or selling prices.
            </p>
          </div>

          <div class="field gst-rate-field">
            <label class="label">GST rate (%)</label>
            <div class="control icons-right">
              <input
                class="input"
                :class="{ 'input-error': errors.gst_rate }"
                type="number"
                min="0"
                max="100"
                step="0.01"
                v-model.number="form.gst_rate">
              <span class="icon right">
                <i class="mdi mdi-percent"></i>
              </span>
            </div>
            <p v-if="errors.gst_rate" class="help help-error">{{ errors.gst_rate[0] }}</p>
            <p class="help">The current NZ standard rate is 15%.</p>
          </div>

          <div class="field">
            <label class="label">Supplier and item prices</label>
            <div class="control">
              <label class="checkbox">
                <input type="checkbox" v-model="form.prices_include_gst">
                <span class="check"></span>
                <span class="control-label">Prices I enter for items already include GST</span>
              </label>
            </div>
            <p class="help">
              When this is on, item prices are converted back to GST-exclusive amounts
              before recipe costing. When it is off, prices are treated as GST-exclusive
              and GST is added on top.
            </p>
          </div>

          <div class="preview-panel">
            <h4 class="preview-title">
              <span class="icon"><i class="mdi mdi-calculator"></i></span>
              How your costs will be shown
            </h4>
            <p class="preview-intro">
              Using an example item price of <strong>$100.00</strong>:
            </p>
            <div class="preview-grid">
              <div>
                <p class="preview-label">Cost excl. GST</p>
                <p class="preview-value">${{ preview.exclusive.toFixed(2) }}</p>
              </div>
              <div>
                <p class="preview-label">GST ({{ effectiveRateLabel }}%)</p>
                <p class="preview-value">${{ preview.gst.toFixed(2) }}</p>
              </div>
              <div>
                <p class="preview-label">Cost incl. GST</p>
                <p class="preview-value">${{ preview.inclusive.toFixed(2) }}</p>
              </div>
            </div>
          </div>

          <div class="field grouped mt-5">
            <div class="control">
              <button
                type="submit"
                class="button green"
                :class="{ 'is-loading': isSaving }"
                :disabled="isSaving">
                Save GST Settings
              </button>
            </div>
            <div class="control">
              <router-link to="/" class="button">
                Cancel
              </router-link>
            </div>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import axios from 'axios'
import { useFlashMessage } from '@/composables/useFlashMessage.js'

const { success, error } = useFlashMessage()

const form = reactive({
  gst_registered: true,
  gst_rate: 15,
  prices_include_gst: false
})

const isLoading = ref(true)
const isSaving = ref(false)
const errors = ref({})

const effectiveRate = computed(() => {
  return form.gst_registered ? Number(form.gst_rate) || 0 : 0
})

const effectiveRateLabel = computed(() => {
  return Number(effectiveRate.value.toFixed(2)).toString()
})

const preview = computed(() => {
  const entered = 100
  const rate = effectiveRate.value

  if (rate <= 0) {
    return { exclusive: entered, gst: 0, inclusive: entered }
  }

  if (form.prices_include_gst) {
    const exclusive = entered / (1 + rate / 100)
    return {
      exclusive,
      gst: entered - exclusive,
      inclusive: entered
    }
  }

  const gst = entered * (rate / 100)
  return {
    exclusive: entered,
    gst,
    inclusive: entered + gst
  }
})

const applySettings = (settings) => {
  form.gst_registered = Boolean(settings.gst_registered)
  form.gst_rate = Number(settings.gst_rate)
  form.prices_include_gst = Boolean(settings.prices_include_gst)
}

const fetchSettings = async () => {
  isLoading.value = true
  try {
    const response = await axios.get('/api/settings/gst')
    applySettings(response.data.settings)
  } catch (fetchError) {
    console.error('Error fetching GST settings:', fetchError)
    error('Loading Error', 'Could not load GST settings. Please refresh the page.')
  } finally {
    isLoading.value = false
  }
}

const saveSettings = async () => {
  isSaving.value = true
  errors.value = {}

  try {
    const response = await axios.put('/api/settings/gst', {
      gst_registered: form.gst_registered,
      gst_rate: form.gst_rate,
      prices_include_gst: form.prices_include_gst
    })

    applySettings(response.data.settings)
    success('GST Settings Saved', 'Your GST settings have been updated.')
  } catch (saveError) {
    console.error('Error saving GST settings:', saveError)

    if (saveError.response?.status === 422) {
      errors.value = saveError.response.data.errors || {}
      error('Validation Error', 'Please check the GST settings and try again.')
    } else {
      error('Save Failed', 'Could not save GST settings. Please try again.')
    }
  } finally {
    isSaving.value = false
  }
}

onMounted(fetchSettings)
</script>

<style scoped>
.nz-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 9999px;
  background-color: #dcfce7;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #166534;
}

.settings-note {
  border: 1px solid #bfdbfe;
  border-radius: 0.375rem;
  background-color: #eff6ff;
  padding: 0.75rem 1rem;
  margin-bottom: 1.25rem;
  color: #1e3a8a;
}

.gst-rate-field {
  max-width: 22rem;
}

.input-error {
  border-color: #ef4444 !important;
}

.help-error {
  color: #dc2626;
}

.preview-panel {
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background-color: #f9fafb;
  padding: 1rem 1.25rem;
}

.preview-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
}

.preview-intro {
  margin-top: 0.5rem;
  margin-bottom: 0.75rem;
}

.preview-grid {
  display: grid;
  grid-template-columns: repeat(1, minmax(0, 1fr));
  gap: 1rem;
  text-align: center;
}

.preview-label {
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  color: #6b7280;
}

.preview-value {
  margin-top: 0.25rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

@media (min-width: 768px) {
  .preview-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
