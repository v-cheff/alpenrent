<script setup>
import { computed, onMounted, reactive } from 'vue'
import { catalogTabs } from '../data/catalog.js'
import { createEquipment, getEquipment } from '../api/equipment.js'

const todayIso = new Date().toISOString().slice(0, 10)

const blankForm = () => ({
  name: '',
  category: 'ski',
  categoryLabel: '',
  badge: '',
  badgeTone: 'new',
  image: '',
  specs: [''],
  dailyRate: 0,
  deposit: 0,
  sizes: [''],
  availableNow: true,
  availableDates: [''],
})

const state = reactive({
  equipment: [],
  activeTab: 'all',
  availabilityMode: 'all',
  selectedDate: todayIso,
  modalOpen: false,
  form: blankForm(),
  imagePreview: '',
  imageFile: null,
  formError: '',
  loadError: '',
  isLoading: true,
  isSaving: false,
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
  state.form = blankForm()
  state.imagePreview = ''
  state.imageFile = null
  state.formError = ''
  state.modalOpen = true
}

function closeModal() {
  if (state.imagePreview.startsWith('blob:')) {
    URL.revokeObjectURL(state.imagePreview)
  }
  state.modalOpen = false
  state.form = blankForm()
  state.imagePreview = ''
  state.imageFile = null
  state.formError = ''
}

async function loadEquipment() {
  state.isLoading = true
  state.loadError = ''

  try {
    state.equipment = await getEquipment()
  } catch (error) {
    state.equipment = []
    state.loadError = error.message || 'Could not load equipment.'
  } finally {
    state.isLoading = false
  }
}

function addListItem(field, maxItems = Infinity) {
  if (state.form[field].length < maxItems) {
    state.form[field].push('')
  }
}

function removeListItem(field, index) {
  state.form[field].splice(index, 1)
}

function handleImageUpload(event) {
  const file = event.target.files?.[0]
  if (!file) {
    return
  }

  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    state.formError = 'Choose a JPG, PNG, or WEBP image.'
    event.target.value = ''
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    state.formError = 'Image must be 5 MB or smaller.'
    event.target.value = ''
    return
  }

  state.formError = ''
  state.imageFile = file
  state.form.image = ''
  if (state.imagePreview.startsWith('blob:')) {
    URL.revokeObjectURL(state.imagePreview)
  }
  state.imagePreview = URL.createObjectURL(file)
}

function handleImageUrlInput() {
  state.imageFile = null
  if (state.imagePreview.startsWith('blob:')) {
    URL.revokeObjectURL(state.imagePreview)
  }
  state.imagePreview = state.form.image
}

async function saveEquipment() {
  if (!state.form.name.trim()) {
    state.formError = 'Enter an equipment name.'
    return
  }

  const formData = new FormData()
  const fields = {
    name: state.form.name.trim(),
    category: state.form.category,
    categoryLabel: state.form.categoryLabel.trim(),
    badge: state.form.badge.trim(),
    badgeTone: state.form.badgeTone,
    dailyRate: String(state.form.dailyRate),
    deposit: String(state.form.deposit),
    availableNow: state.form.availableNow ? '1' : '0',
  }

  Object.entries(fields).forEach(([key, value]) => formData.append(key, value))
  for (const [field, values] of Object.entries({
    specs: state.form.specs,
    sizes: state.form.sizes,
    availableDates: state.form.availableDates,
  })) {
    [...new Set(values.map((value) => value.trim()).filter(Boolean))].forEach((value) =>
      formData.append(`${field}[]`, value),
    )
  }

  if (state.imageFile) {
    formData.append('imageFile', state.imageFile)
  } else if (state.form.image.trim()) {
    formData.append('image', state.form.image.trim())
  }

  state.isSaving = true
  state.formError = ''

  try {
    const equipment = await createEquipment(formData)
    state.equipment.unshift(equipment)
    closeModal()
  } catch (error) {
    state.formError = error.message || 'Could not save equipment.'
  } finally {
    state.isSaving = false
  }
}

onMounted(loadEquipment)
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

        <p v-if="state.isLoading" class="mt-6 text-sm text-slate-500">Loading equipment…</p>
        <p
          v-else-if="state.loadError"
          class="mt-6 rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700"
          role="alert"
        >
          Could not load equipment: {{ state.loadError }}
        </p>

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
              Add equipment
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

          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
            <div class="mb-3 flex items-center justify-between gap-3">
              <div>
                <h3 class="text-sm font-semibold text-slate-700">Specs</h3>
                <p class="mt-1 text-xs text-slate-500">Add up to 3 short details.</p>
              </div>
              <span class="text-xs font-medium text-slate-500">
                {{ state.form.specs.filter((spec) => spec.trim()).length }}/3
              </span>
            </div>

            <div class="space-y-2">
              <div
                v-for="(spec, index) in state.form.specs"
                :key="`spec-${index}`"
                class="flex items-center gap-2"
              >
                <input
                  v-model="state.form.specs[index]"
                  type="text"
                  maxlength="80"
                  :aria-label="`Specification ${index + 1}`"
                  :placeholder="index === 0 ? 'Radius 16 m' : 'Another short detail'"
                  class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100"
                />
                <button
                  type="button"
                  :aria-label="`Remove specification ${index + 1}`"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                  @click="removeListItem('specs', index)"
                >
                  −
                </button>
              </div>
            </div>

            <button
              v-if="state.form.specs.length < 3"
              type="button"
              class="mt-3 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-sm font-medium text-cyan-700 transition hover:bg-cyan-50"
              @click="addListItem('specs', 3)"
            >
              <span aria-hidden="true" class="text-base leading-none">+</span>
              Add spec
            </button>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
            <div class="mb-3">
              <h3 class="text-sm font-semibold text-slate-700">Sizes</h3>
              <p class="mt-1 text-xs text-slate-500">Add each available size separately.</p>
            </div>

            <div class="flex flex-wrap gap-2">
              <div
                v-for="(size, index) in state.form.sizes"
                :key="`size-${index}`"
                class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1"
              >
                <input
                  v-model="state.form.sizes[index]"
                  type="text"
                  maxlength="32"
                  :aria-label="`Size ${index + 1}`"
                  placeholder="156"
                  class="w-20 rounded-lg px-2 py-1.5 text-center text-sm text-slate-900 outline-none focus:ring-2 focus:ring-cyan-100"
                />
                <button
                  type="button"
                  :aria-label="`Remove size ${index + 1}`"
                  class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                  @click="removeListItem('sizes', index)"
                >
                  −
                </button>
              </div>
            </div>

            <button
              type="button"
              class="mt-3 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-sm font-medium text-cyan-700 transition hover:bg-cyan-50"
              @click="addListItem('sizes')"
            >
              <span aria-hidden="true" class="text-base leading-none">+</span>
              Add size
            </button>
          </section>

          <section class="rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:col-span-2">
            <div class="mb-3">
              <h3 class="text-sm font-semibold text-slate-700">Available dates</h3>
              <p class="mt-1 text-xs text-slate-500">Add dates when this equipment is available.</p>
            </div>

            <div class="space-y-2">
              <div
                v-for="(date, index) in state.form.availableDates"
                :key="`date-${index}`"
                class="flex items-center gap-2"
              >
                <input
                  v-model="state.form.availableDates[index]"
                  type="date"
                  :aria-label="`Available date ${index + 1}`"
                  class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100"
                />
                <button
                  type="button"
                  :aria-label="`Remove available date ${index + 1}`"
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"
                  @click="removeListItem('availableDates', index)"
                >
                  −
                </button>
              </div>
            </div>

            <button
              type="button"
              class="mt-3 inline-flex items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-sm font-medium text-cyan-700 transition hover:bg-cyan-50"
              @click="addListItem('availableDates')"
            >
              <span aria-hidden="true" class="text-base leading-none">+</span>
              Add date
            </button>
          </section>

          <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 lg:col-span-2">
            <input v-model="state.form.availableNow" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
            Available now
          </label>

          <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 lg:col-span-2">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <p class="text-sm font-medium text-slate-700">Equipment image</p>
                <p class="text-xs text-slate-500">JPG, PNG, or WEBP; max 5 MB</p>
              </div>

              <label class="cursor-pointer rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
                Upload image
                <input
                  type="file"
                  accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                  class="hidden"
                  @change="handleImageUpload"
                />
              </label>
            </div>

            <label class="mt-4 block">
              <span class="mb-2 block text-sm font-medium text-slate-700">Image URL</span>
              <input
                v-model="state.form.image"
                type="text"
                placeholder="https://..."
                @input="handleImageUrlInput"
                class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
              />
            </label>

            <div v-if="state.imagePreview || state.form.image" class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
              <img :src="state.imagePreview || state.form.image" alt="Preview" class="h-52 w-full object-cover" />
            </div>
          </div>

          <p
            v-if="state.formError"
            class="rounded-2xl bg-rose-50 px-4 py-3 text-sm text-rose-700 lg:col-span-2"
            role="alert"
          >
            {{ state.formError }}
          </p>

          <div class="flex flex-wrap gap-3 lg:col-span-2">
            <button
              type="submit"
              :disabled="state.isSaving"
              class="rounded-full bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-500"
            >
              {{ state.isSaving ? 'Saving…' : 'Add equipment' }}
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
