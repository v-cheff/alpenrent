import { computed, reactive } from 'vue'
import { getEquipment } from '../../api/equipment.js'

function todayIso() {
  return new Date().toISOString().slice(0, 10)
}

function matchesFilters(item, { activeTab, availabilityMode, selectedDate }) {
  if (activeTab !== 'all' && item.category !== activeTab) {
    return false
  }

  if (availabilityMode === 'current') {
    return item.availableNow
  }

  if (availabilityMode === 'booked') {
    return !item.availableNow
  }

  if (availabilityMode === 'selected') {
    if (!selectedDate) {
      return item.availableNow
    }

    return item.availableDates?.includes(selectedDate) || item.availableNow
  }

  return true
}

export function useEquipmentList() {
  const list = reactive({
    items: [],
    activeTab: 'all',
    availabilityMode: 'all',
    selectedDate: todayIso(),
    isLoading: true,
    loadError: '',
  })

  const filteredItems = computed(() => list.items.filter((item) => matchesFilters(item, list)))

  const stats = computed(() => ({
    total: list.items.length,
    availableNow: list.items.filter((item) => item.availableNow).length,
    selectedDateCount: list.items.filter((item) =>
      item.availableDates?.includes(list.selectedDate),
    ).length,
    categories: new Set(list.items.map((item) => item.category)).size,
  }))

  async function load() {
    list.isLoading = true
    list.loadError = ''

    try {
      const data = await getEquipment()
      list.items = Array.isArray(data) ? data : []
    } catch (error) {
      list.items = []
      list.loadError = error.message || 'Could not load equipment.'
    } finally {
      list.isLoading = false
    }
  }

  function prepend(item) {
    list.items.unshift(item)
  }

  return {
    list,
    filteredItems,
    stats,
    load,
    prepend,
  }
}
