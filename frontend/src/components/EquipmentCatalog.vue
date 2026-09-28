<script setup>
import { computed, ref, onMounted } from 'vue'
import { catalogTabs, equipment } from '../data/catalog'
import { useBooking } from '../composables/useBooking'
import ProductCard from './ProductCard.vue'

const { state } = useBooking()
const activeTab = ref('all')

const filtered = computed(() => {
  if (activeTab.value === 'all') return equipment
  return equipment.filter((item) => item.category === activeTab.value)
})

const product = ref([])

const loadData = async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/v1/equipment')
    if (res.ok) {
      product.value = await res.json()
      console.log('Fetched data:', product.value)
    } else {
      console.error('Ошибка сервера:', res.status)
    }
  } catch (err) {
    console.error('Ошибка сети/CORS:', err)
  }
}

onMounted(() => {
  loadData()
})

</script>

<template>
  <section id="catalog" class="relative z-10 -mt-6 bg-slate-50 pb-20 pt-4 sm:pt-8">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <p class="text-xs font-bold uppercase tracking-[0.2em] text-alpine-700">Interactive catalog</p>
          <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slateink sm:text-4xl">
            Tuned gear, ready to ride
          </h2>
          <p v-if="state.availabilityChecked" class="mt-2 text-sm font-medium text-emerald-700">
            Availability confirmed for {{ state.startDate }} → {{ state.endDate }} at your selected store.
          </p>
        </div>
        <p class="max-w-sm text-sm leading-relaxed text-slate-500">
          Daily rates shown include 20% Austrian VAT (MwSt.). Deposits are authorized, not charged unless damaged.
        </p>
      </div>

      <div class="mb-8 flex gap-2 overflow-x-auto no-scrollbar pb-1">
        <button
          v-for="tab in catalogTabs"
          :key="tab.id"
          type="button"
          class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition"
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

      <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <ProductCard v-for="item in filtered" :key="item.id" :item="item" />
      </div>
    </div>
  </section>
</template>
