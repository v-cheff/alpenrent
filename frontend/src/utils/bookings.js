function todayIso() {
  const now = new Date()
  const pad = (n) => String(n).padStart(2, '0')
  return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`
}

/** 'upcoming' | 'active' | 'completed' relative to today. */
export function bookingStatus(booking, today = todayIso()) {
  if (booking.endDate < today) return 'completed'
  if (booking.startDate <= today) return 'active'
  return 'upcoming'
}

export function bookingDays(booking) {
  const start = new Date(`${booking.startDate}T12:00:00`)
  const end = new Date(`${booking.endDate}T12:00:00`)
  return Math.max(Math.round((end - start) / 86400000), 1)
}

export function formatBookingDate(iso) {
  return new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }).format(
    new Date(`${iso}T12:00:00`),
  )
}
