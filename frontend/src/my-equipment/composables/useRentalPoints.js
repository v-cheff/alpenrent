import { computed, reactive } from 'vue'
import {
  createRentalPoint,
  deleteRentalPoint,
  getMyRentalPoints,
  updateRentalPoint,
} from '../../api/rentalPoints.js'

export function useRentalPoints() {
  const points = reactive({
    items: [],
    isLoading: true,
    loadError: '',
  })

  const byId = computed(() => new Map(points.items.map((point) => [point.id, point])))

  async function load() {
    points.isLoading = true
    points.loadError = ''

    try {
      const data = await getMyRentalPoints()
      points.items = Array.isArray(data) ? data : []
    } catch (error) {
      points.items = []
      points.loadError = error.message || 'Could not load rental points.'
    } finally {
      points.isLoading = false
    }
  }

  function sortItems() {
    points.items.sort((a, b) => a.name.localeCompare(b.name))
  }

  async function save(id, data) {
    if (id) {
      const updated = await updateRentalPoint(id, data)
      const index = points.items.findIndex((point) => point.id === id)
      if (index !== -1) points.items[index] = updated
      sortItems()
      return updated
    }

    const created = await createRentalPoint(data)
    points.items.push(created)
    sortItems()
    return created
  }

  async function remove(id) {
    await deleteRentalPoint(id)
    points.items = points.items.filter((point) => point.id !== id)
  }

  /**
   * Keep the per-point equipment counters in sync without refetching.
   */
  function adjustEquipmentCount(pointId, delta) {
    const point = byId.value.get(pointId)
    if (point) {
      point.equipmentCount = Math.max(0, (point.equipmentCount ?? 0) + delta)
    }
  }

  return {
    points,
    byId,
    load,
    save,
    remove,
    adjustEquipmentCount,
  }
}
