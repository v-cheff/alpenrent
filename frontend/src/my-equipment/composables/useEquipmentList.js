import { computed, reactive } from 'vue'
import { getMyEquipment, deleteEquipment } from '../../api/equipment.js'

function todayIso() {
  return new Date().toISOString().slice(0, 10)
}

function matchesFilters(item, { activeTab, activeRentalPointId, availabilityMode, selectedDate }) {
  if (activeRentalPointId !== null && item.rentalPointId !== activeRentalPointId) {
    return false
  }

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
    // null = equipment from every rental point
    activeRentalPointId: null,
    availabilityMode: 'all',
    selectedDate: todayIso(),
    isLoading: true,
    loadError: '',
  })

  const filteredItems = computed(() => list.items.filter((item) => matchesFilters(item, list)))

  // Stats follow the selected rental point so an owner can see each shop on its own.
  const pointItems = computed(() =>
    list.activeRentalPointId === null
      ? list.items
      : list.items.filter((item) => item.rentalPointId === list.activeRentalPointId),
  )

  const stats = computed(() => ({
    total: pointItems.value.length,
    availableNow: pointItems.value.filter((item) => item.availableNow).length,
    selectedDateCount: pointItems.value.filter((item) =>
      item.availableDates?.includes(list.selectedDate),
    ).length,
  }))

  async function load() {
    list.isLoading = true
    list.loadError = ''

    try {
      const data = await getMyEquipment()
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

  function updateItem(updatedItem) {
    const index = list.items.findIndex((item) => item.id === updatedItem.id)
    if (index !== -1) {
      list.items[index] = updatedItem
    }
  }

  function removeItem(id) {
    list.items = list.items.filter((item) => item.id !== id)
  }

  async function deleteEquipmentItem(id) {
    await deleteEquipment(id)
    removeItem(id)
  }

  return {
    list,
    filteredItems,
    stats,
    load,
    prepend,
    updateItem,
    removeItem,
    deleteEquipmentItem,
  }
}
