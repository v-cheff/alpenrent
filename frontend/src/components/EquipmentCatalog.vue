
<script setup>
import { computed, onMounted, ref } from 'vue'
import { catalogTabs } from '../data/catalog'
import { useBooking } from '../composables/useBooking'
import ProductCard from './ProductCard.vue'
import Spinner from './Spinner.vue'

const { state } = useBooking()

const activeTab = ref('all')
const equipment = ref([])
const isLoading = ref(false)
const error = ref('')

const loadEquipment = async () => {
  isLoading.value = true
  error.value = ''

  try {
    const response = await fetch(
      'http://127.0.0.1:8000/v1/equipment'
    )

    if (!response.ok) {
      throw new Error(`HTTP error: ${response.status}`)
    }

    const data = await response.json()

    if (!Array.isArray(data)) {
      throw new Error('Invalid equipment data format')
    }

    equipment.value = data
  } catch (err) {
    console.error('Failed to load equipment:', err)
    error.value = 'Unable to load products. Please try again.'
  } finally {
    isLoading.value = false
  }
}

const filtered = computed(() => {
  if (activeTab.value === 'all') {
    return equipment.value
  }

  return equipment.value.filter(
    (item) => item.category === activeTab.value
  )
})

onMounted(loadEquipment)
</script>

<template>
  <section
    id="catalog"
    class="relative z-10 -mt-6 bg-slate-50 pb-20 pt-4 sm:pt-8"
  >
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div
        class="mb-8 flex flex-col gap-4 sm:flex-row
               sm:items-end sm:justify-between"
      >
        <div>
          <p
            class="text-xs font-bold uppercase tracking-[0.2em]
                   text-alpine-700"
          >
            Interactive catalog
          </p>

          <h2
            class="mt-2 text-3xl font-extrabold tracking-tight
                   text-slateink sm:text-4xl"
          >
            Tuned gear, ready to ride
          </h2>

          <p
            v-if="state.availabilityChecked"
            class="mt-2 text-sm font-medium text-emerald-700"
          >
            Availability confirmed for
            {{ state.startDate }} → {{ state.endDate }}
            at your selected store.
          </p>
        </div>

        <p class="max-w-sm text-sm leading-relaxed text-slate-500">
          Daily rates shown include 20% Austrian VAT (MwSt.).
          Deposits are authorized, not charged unless damaged.
        </p>
      </div>

      <div
        class="mb-8 flex gap-2 overflow-x-auto
               no-scrollbar pb-1"
      >
        <button
          v-for="tab in catalogTabs"
          :key="tab.id"
          type="button"
          :aria-pressed="activeTab === tab.id"
          class="whitespace-nowrap rounded-full px-4 py-2
                 text-sm font-semibold transition"
          :class="
            activeTab === tab.id
              ? 'bg-slateink text-white shadow-sm'
              : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-100'
          "
          @click="activeTab = tab.id"
        >
          {{ tab.label }}
        </button>
      </div>

      <div
        v-if="isLoading"
        class="flex justify-center py-12"
        role="status"
        aria-label="Loading products"
      >
        <Spinner />
      </div>

      <div
        v-else-if="error"
        class="flex flex-col items-center gap-4 py-20 text-center"
        role="alert"
      >
        <p class="text-lg font-semibold text-slateink">
          {{ error }}
        </p>

        <button
          type="button"
          class="rounded-full bg-slateink px-5 py-2
                 text-sm font-semibold text-white
                 transition hover:opacity-90"
          @click="loadEquipment"
        >
          Try again
        </button>
      </div>

      <div
        v-else-if="filtered.length"
        class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
      >
        <ProductCard
          v-for="item in filtered"
          :key="item.id"
          :item="item"
        />
      </div>

      <div
        v-else
        class="flex flex-col items-center justify-center
               gap-4 py-20 text-center"
      >
        <p class="text-lg font-semibold text-slateink">
          No products available in this category.
        </p>

        <p class="max-w-sm text-sm leading-relaxed text-slate-500">
          Please check back later or select a different category.
        </p>
      </div>
    </div>
  </section>
</template>
