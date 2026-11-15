import { catalogTabs } from '../data/catalog.js'

export const equipmentCategoryOptions = catalogTabs.filter((tab) => tab.id !== 'all')

export const badgeToneOptions = [
  { id: 'new', label: 'new' },
  { id: 'waxed', label: 'waxed' },
  { id: 'info', label: 'info' },
  { id: 'kids', label: 'kids' },
]

export const badgeToneClass = {
  new: 'bg-emerald-100 text-emerald-700',
  waxed: 'bg-amber-100 text-amber-700',
  kids: 'bg-violet-100 text-violet-700',
  info: 'bg-cyan-100 text-cyan-700',
}

export const availabilityModeOptions = [
  { id: 'all', label: 'All' },
  { id: 'current', label: 'Available now' },
  { id: 'selected', label: 'Selected period' },
  { id: 'booked', label: 'Booked' },
]

export const MAX_SPECS = 3
export const MAX_IMAGE_BYTES = 5 * 1024 * 1024
export const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp']
