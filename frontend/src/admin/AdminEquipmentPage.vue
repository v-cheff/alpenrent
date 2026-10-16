<script setup>
import { computed, reactive } from 'vue'
import { catalogTabs } from '../data/catalog.js'

const todayIso = new Date().toISOString().slice(0, 10)

const equipmentSeed = [
  {
    id: 'atomic-redster',
    name: 'Atomic Redster S9 Revoshock',
    category: 'ski',
    categoryLabel: 'Performance Ski Set',
    badge: 'Model 2026',
    badgeTone: 'new',
    image:
      'https://images.unsplash.com/photo-1551698618-1dfe5d97d256?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 13.5 m', 'Camber race profile', 'Includes poles'],
    dailyRate: 49,
    deposit: 150,
    sizes: ['150', '157', '165', '172'],
    availableNow: true,
    availableDates: ['2026-10-16', '2026-10-18', '2026-10-20'],
  },
  {
    id: 'rossignol-experience',
    name: 'Rossignol Experience 86 Basalt',
    category: 'ski',
    categoryLabel: 'All-Mountain Ski Set',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1605540436563-5bca919ae766?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 16 m', 'Air Tip rocker', 'ISO 11088 bindings'],
    dailyRate: 39,
    deposit: 80,
    sizes: ['146', '154', '162', '170', '178'],
    availableNow: false,
    availableDates: ['2026-10-17', '2026-10-21'],
  },
  {
    id: 'head-kore',
    name: 'HEAD Kore 93',
    category: 'ski',
    categoryLabel: 'Freeride Ski Set',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1483664852095-d6cc6870703d?auto=format&fit=crop&w=900&q=80',
    specs: ['Radius 17.8 m', 'Tip & tail rocker', 'Carbon sandwich'],
    dailyRate: 45,
    deposit: 120,
    sizes: ['156', '163', '170', '177', '184'],
    availableNow: true,
    availableDates: ['2026-10-16', '2026-10-19', '2026-10-22'],
  },
  {
    id: 'burton-custom',
    name: 'Burton Custom Camber',
    category: 'board',
    categoryLabel: 'All-Mountain Snowboard',
    badge: 'Model 2026',
    badgeTone: 'new',
    image:
      'https://images.unsplash.com/photo-1522056615691-da7b8106c665?auto=format&fit=crop&w=900&q=80',
    specs: ['Camber pop', 'Directional twin', 'Channel bindings'],
    dailyRate: 42,
    deposit: 100,
    sizes: ['148', '152', '156', '158W', '162'],
    availableNow: true,
    availableDates: ['2026-10-16', '2026-10-17', '2026-10-23'],
  },
  {
    id: 'jones-mind',
    name: 'Jones Mind Expander',
    category: 'board',
    categoryLabel: 'Powder Snowboard',
    badge: 'Freshly Waxed',
    badgeTone: 'waxed',
    image:
      'https://images.unsplash.com/photo-1518085250887-2f833c18b9d4?auto=format&fit=crop&w=900&q=80',
    specs: ['Spoon 3.0 nose', '3D contour base', 'Swallow tail'],
    dailyRate: 48,
    deposit: 130,
    sizes: ['146', '150', '154', '158'],
    availableNow: false,
    availableDates: ['2026-10-22', '2026-10-23'],
  },
  {
    id: 'lange-shadow',
    name: 'Lange Shadow 120 LV',
    category: 'boots',
    categoryLabel: 'Alpine Ski Boots',
    badge: 'Heat-moldable',
    badgeTone: 'info',
    image:
      'https://images.unsplash.com/photo-1578662996442-48f36e048e72?auto=format&fit=crop&w=900&q=80',
    specs: ['Flex 120', 'Last 97 mm', 'GripWalk soles'],
    dailyRate: 22,
    deposit: 50,
    sizes: ['24.5', '25.5', '26.5', '27.5', '28.5'],
    availableNow: true,
    availableDates: ['2026-10-16', '2026-10-18', '2026-10-19'],
  },
  {
    id: 'poc-obex',
    name: 'POC Obex BC MIPS + Visor',
    category: 'boots',
    categoryLabel: 'Helmet & Protection',
    badge: 'MIPS',
    badgeTone: 'info',
    image:
      'https://images.unsplash.com/photo-1516731415730-0c1374e67dba?auto=format&fit=crop&w=900&q=80',
    specs: ['MIPS liner', 'RECCO reflector', 'Category 3 visor'],
    dailyRate: 12,
    deposit: 40,
    sizes: ['XS/S', 'M/L', 'XL'],
    availableNow: true,
    availableDates: ['2026-10-16', '2026-10-17'],
  },
  {
    id: 'kids-atomic',
    name: 'Atomic Maven Girl / Redster J',
    category: 'kids',
    categoryLabel: 'Kids Ski Set',
    badge: 'Kids 4–12',
    badgeTone: 'kids',
    image:
      'https://images.unsplash.com/photo-1548777123-e27896422455?auto=format&fit=crop&w=900&q=80',
    specs: ['Soft flex bindings', 'Helmet included', 'Parent-friendly size chart'],
    dailyRate: 24,
    deposit: 40,
    sizes: ['90', '100', '110', '120', '130'],
    availableNow: false,
    availableDates: ['2026-10-17', '2026-10-18'],
  },
  {
    id: 'kids-burton',
    name: 'Burton Mini Grom Package',
    category: 'kids',
    categoryLabel: 'Kids Snowboard Set',
    badge: 'Kids 5–12',
    badgeTone: 'kids',
    image:
      'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=80',
    specs: ['Soft bindings', 'Easy-entry boots', 'Park-ready twin'],
    dailyRate: 26,
    deposit: 45,
    sizes: ['110', '120', '130', '134'],
    availableNow: true,
    availableDates: ['2026-10-18', '2026-10-19', '2026-10-20'],
  },
]

const blankForm = () => ({
  id: '',
  name: '',
  category: 'ski',
  categoryLabel: '',
  badge: '',
  badgeTone: 'new',
  image: '',
  specs: '',
  dailyRate: 0,
  deposit: 0,
  sizes: '',
  availableNow: true,
  availableDates: '',
})

const state = reactive({
  equipment: [...equipmentSeed],
  activeTab: 'all',
  availabilityMode: 'all',
  selectedDate: todayIso,
  modalOpen: false,
  modalMode: 'create',
  selectedId: null,
  form: blankForm(),
  imagePreview: '',
})

const filteredItems = computed(() => {
  let items = [...state.equipment]

  if (state.activeTab !== 'all') {
    items = items.filter((item) => item.category === state.activeTab)
  }

  if (state.availabilityMode === 'current') {
    items = items.filter((item) => item.availableNow)
  }

  if (state.availabilityMode === 'selected') {
    items = items.filter((item) => {
      if (!state.selectedDate) {
        return item.availableNow
      }

      const selectedDate = state.selectedDate
      return item.availableDates?.includes(selectedDate) || item.availableNow
    })
  }

  if (state.availabilityMode === 'booked') {
    items = items.filter((item) => !item.availableNow)
  }

  return items
})

const stats = computed(() => ({
  total: state.equipment.length,
  availableNow: state.equipment.filter((item) => item.availableNow).length,
  selectedDateCount: state.equipment.filter((item) =>
    item.availableDates?.includes(state.selectedDate),
  ).length,
  categories: new Set(state.equipment.map((item) => item.category)).size,
}))

function openCreateModal() {
  state.modalMode = 'create'
  state.selectedId = null
  state.form = blankForm()
  state.imagePreview = ''
  state.modalOpen = true
}

function openEditModal(item) {
  state.modalMode = 'edit'
  state.selectedId = item.id
  state.form = {
    ...item,
    specs: Array.isArray(item.specs) ? item.specs.join('\n') : '',
    sizes: Array.isArray(item.sizes) ? item.sizes.join('\n') : '',
    availableDates: Array.isArray(item.availableDates) ? item.availableDates.join('\n') : '',
  }
  state.imagePreview = item.image || ''
  state.modalOpen = true
}

function closeModal() {
  state.modalOpen = false
  state.modalMode = 'create'
  state.selectedId = null
  state.form = blankForm()
  state.imagePreview = ''
}

function parseList(value) {
  return value
    .split('\n')
    .map((item) => item.trim())
    .filter(Boolean)
}

function handleImageUpload(event) {
  const file = event.target.files?.[0]
  if (!file) {
    return
  }

  const reader = new FileReader()
  reader.onload = (loadEvent) => {
    state.form.image = loadEvent.target?.result ?? ''
    state.imagePreview = state.form.image
  }
  reader.readAsDataURL(file)
}

function saveEquipment() {
  if (!state.form.name.trim()) {
    return
  }

  const payload = {
    id: state.form.id ? state.form.id.trim() : `item-${Date.now()}`,
    name: state.form.name.trim(),
    category: state.form.category.trim(),
    categoryLabel: state.form.categoryLabel.trim() || 'Custom equipment',
    badge: state.form.badge.trim() || 'New',
    badgeTone: state.form.badgeTone || 'new',
    image:
      state.form.image ||
      'https://images.unsplash.com/photo-1517849845537-4d257902454a?auto=format&fit=crop&w=900&q=80',
    specs: parseList(state.form.specs),
    dailyRate: Number(state.form.dailyRate) || 0,
    deposit: Number(state.form.deposit) || 0,
    sizes: parseList(state.form.sizes),
    availableNow: state.form.availableNow,
    availableDates: parseList(state.form.availableDates),
  }

  if (state.modalMode === 'edit' && state.selectedId) {
    const index = state.equipment.findIndex((item) => item.id === state.selectedId)
    if (index >= 0) {
      state.equipment[index] = { ...payload, id: state.selectedId }
    }
  } else {
    state.equipment.unshift(payload)
  }

  closeModal()
}

function removeEquipment(id) {
  state.equipment = state.equipment.filter((item) => item.id !== id)
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur">
      <div class="mx-auto flex max-w-[1600px] items-center justify-between gap-4 px-5 py-5 lg:px-8">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-600">Alpenrent</p>
          <h1 class="mt-1 text-2xl font-bold text-slate-900">Admin equipment dashboard</h1>
        </div>

        <div class="flex items-center gap-3">
          <div class="flex items-center gap-3 rounded-full border border-slate-200 bg-slate-50 px-4 py-2">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span class="text-sm font-medium text-slate-700">System online</span>
          </div>

          <button
            type="button"
            class="rounded-full bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
            @click="openCreateModal"
          >
            Add equipment
          </button>
        </div>
      </div>
    </header>

    <main class="mx-auto max-w-[1600px] px-5 py-8 lg:px-8">
      <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Total equipment</p>
          <div class="mt-3 flex items-end justify-between">
            <strong class="text-3xl font-bold text-slate-900">{{ stats.total }}</strong>
            <span class="rounded-full bg-cyan-50 px-2 py-1 text-xs font-semibold text-cyan-700">Live</span>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Available now</p>
          <div class="mt-3 flex items-end justify-between">
            <strong class="text-3xl font-bold text-slate-900">{{ stats.availableNow }}</strong>
            <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">Open</span>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Selected date</p>
          <div class="mt-3 flex items-end justify-between">
            <strong class="text-3xl font-bold text-slate-900">{{ stats.selectedDateCount }}</strong>
            <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Slots</span>
          </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm text-slate-500">Categories</p>
          <div class="mt-3 flex items-end justify-between">
            <strong class="text-3xl font-bold text-slate-900">{{ stats.categories }}</strong>
            <span class="rounded-full bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700">Groups</span>
          </div>
        </div>
      </section>

      <section class="mt-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="tab in catalogTabs"
              :key="tab.id"
              type="button"
              class="rounded-full px-4 py-2 text-sm font-medium transition"
              :class="
                state.activeTab === tab.id
                  ? 'bg-slate-900 text-white shadow-sm'
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              "
              @click="state.activeTab = tab.id"
            >
              {{ tab.label }}
            </button>
          </div>

          <div class="flex flex-wrap items-center gap-3">
            <label class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
              <span>Availability</span>
              <select v-model="state.availabilityMode" class="bg-transparent outline-none">
                <option value="all">All</option>
                <option value="current">Available now</option>
                <option value="selected">Selected period</option>
                <option value="booked">Booked</option>
              </select>
            </label>

            <label
              v-if="state.availabilityMode === 'selected'"
              class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700"
            >
              <span>Period</span>
              <input v-model="state.selectedDate" type="date" class="bg-transparent outline-none" />
            </label>
          </div>
        </div>

        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
          <article
            v-for="item in filteredItems"
            :key="item.id"
            class="overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 shadow-sm transition hover:-translate-y-1 hover:shadow-md"
          >
            <div class="relative h-52 overflow-hidden">
              <img :src="item.image" :alt="item.name" class="h-full w-full object-cover" />
              <div class="absolute left-3 top-3 flex items-center gap-2">
                <span
                  :class="[
                    'rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]',
                    item.badgeTone === 'new'
                      ? 'bg-emerald-100 text-emerald-700'
                      : item.badgeTone === 'waxed'
                        ? 'bg-amber-100 text-amber-700'
                        : item.badgeTone === 'kids'
                          ? 'bg-violet-100 text-violet-700'
                          : 'bg-cyan-100 text-cyan-700',
                  ]"
                >
                  {{ item.badge }}
                </span>
              </div>
              <div class="absolute right-3 top-3 rounded-full bg-white/90 px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-700">
                {{ item.category }}
              </div>
            </div>

            <div class="p-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-xs uppercase tracking-[0.16em] text-slate-400">{{ item.categoryLabel }}</p>
                  <h3 class="mt-2 text-lg font-bold text-slate-900">{{ item.name }}</h3>
                </div>
                <span
                  :class="[
                    'rounded-full px-2 py-1 text-[10px] font-semibold uppercase tracking-[0.14em]',
                    item.availableNow
                      ? 'bg-emerald-100 text-emerald-700'
                      : 'bg-rose-100 text-rose-700',
                  ]"
                >
                  {{ item.availableNow ? 'Available' : 'Booked' }}
                </span>
              </div>

              <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-3">
                <div>
                  <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Daily rate</p>
                  <p class="mt-1 text-xl font-bold text-cyan-700">€{{ item.dailyRate }}</p>
                </div>
                <div class="text-right">
                  <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Deposit</p>
                  <p class="mt-1 text-base font-semibold text-slate-900">€{{ item.deposit }}</p>
                </div>
              </div>

              <div class="mt-4 flex flex-wrap gap-2">
                <span
                  v-for="size in item.sizes.slice(0, 4)"
                  :key="`${item.id}-${size}`"
                  class="rounded-full border border-slate-200 bg-white px-2 py-1 text-[10px] font-medium text-slate-700"
                >
                  {{ size }}
                </span>
              </div>

              <div class="mt-5 flex gap-2">
                <button
                  type="button"
                  class="flex-1 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100"
                  @click="openEditModal(item)"
                >
                  Edit
                </button>
                <button
                  type="button"
                  class="flex-1 rounded-full bg-rose-50 px-3 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-100"
                  @click="removeEquipment(item.id)"
                >
                  Delete
                </button>
              </div>
            </div>
          </article>
        </div>
      </section>
    </main>

    <div
      v-if="state.modalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
      @click.self="closeModal"
    >
      <div class="max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-center justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Equipment</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">
              {{ state.modalMode === 'edit' ? 'Edit equipment' : 'Add equipment' }}
            </h2>
          </div>

          <button
            type="button"
            class="rounded-full border border-slate-200 bg-slate-100 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
            @click="closeModal"
          >
            Close
          </button>
        </div>

        <form class="mt-6 grid gap-5 lg:grid-cols-2" @submit.prevent="saveEquipment">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">ID</span>
            <input
              v-model="state.form.id"
              type="text"
              placeholder="atomic-redster"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Category</span>
            <select
              v-model="state.form.category"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            >
              <option value="ski">ski</option>
              <option value="board">board</option>
              <option value="boots">boots</option>
              <option value="kids">kids</option>
            </select>
          </label>

          <label class="block lg:col-span-2">
            <span class="mb-2 block text-sm font-medium text-slate-700">Name</span>
            <input
              v-model="state.form.name"
              type="text"
              placeholder="Rossignol Experience 86 Basalt"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Category label</span>
            <input
              v-model="state.form.categoryLabel"
              type="text"
              placeholder="All-Mountain Ski Set"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Badge</span>
            <input
              v-model="state.form.badge"
              type="text"
              placeholder="Freshly Waxed"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Badge tone</span>
            <select
              v-model="state.form.badgeTone"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            >
              <option value="new">new</option>
              <option value="waxed">waxed</option>
              <option value="info">info</option>
              <option value="kids">kids</option>
            </select>
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Daily rate</span>
            <input
              v-model.number="state.form.dailyRate"
              type="number"
              min="0"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Deposit</span>
            <input
              v-model.number="state.form.deposit"
              type="number"
              min="0"
              class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            />
          </label>

          <label class="block lg:col-span-2">
            <span class="mb-2 block text-sm font-medium text-slate-700">Specs</span>
            <textarea
              v-model="state.form.specs"
              rows="4"
              placeholder="Radius 16 m\nAir Tip rocker"
              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            ></textarea>
          </label>

          <label class="block lg:col-span-2">
            <span class="mb-2 block text-sm font-medium text-slate-700">Sizes</span>
            <textarea
              v-model="state.form.sizes"
              rows="3"
              placeholder="146\n154\n162\n170"
              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            ></textarea>
          </label>

          <label class="block lg:col-span-2">
            <span class="mb-2 block text-sm font-medium text-slate-700">Available dates</span>
            <textarea
              v-model="state.form.availableDates"
              rows="3"
              placeholder="2026-10-16\n2026-10-17\n2026-10-18"
              class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
            ></textarea>
          </label>

          <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 lg:col-span-2">
            <input v-model="state.form.availableNow" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
            Available now
          </label>

          <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 lg:col-span-2">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="text-sm font-medium text-slate-700">Equipment image</p>
                <p class="text-xs text-slate-500">Supports JPG, PNG, WEBP</p>
              </div>

              <label class="cursor-pointer rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
                Upload image
                <input type="file" accept="image/*" class="hidden" @change="handleImageUpload" />
              </label>
            </div>

            <label class="mt-4 block">
              <span class="mb-2 block text-sm font-medium text-slate-700">Image URL</span>
              <input
                v-model="state.form.image"
                type="text"
                placeholder="https://..."
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
              />
            </label>

            <div v-if="state.imagePreview || state.form.image" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <img :src="state.imagePreview || state.form.image" alt="Preview" class="h-52 w-full object-cover" />
            </div>
          </div>

          <div class="flex flex-wrap gap-3 lg:col-span-2">
            <button
              type="submit"
              class="rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
            >
              {{ state.modalMode === 'edit' ? 'Save changes' : 'Add equipment' }}
            </button>

            <button
              type="button"
              class="rounded-full border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
              @click="closeModal"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
