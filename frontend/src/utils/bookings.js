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

/** Rental days, inclusive (1–4 Dec = 4). The API sends `days`; the fallback mirrors it. */
export function bookingDays(booking) {
  if (typeof booking.days === 'number') return booking.days
  const start = new Date(`${booking.startDate}T12:00:00`)
  const end = new Date(`${booking.endDate}T12:00:00`)
  return Math.max(Math.round((end - start) / 86400000) + 1, 1)
}

/** "Half day" or "4 days". */
export function bookingDurationLabel(booking) {
  if (booking.rentalType === 'half_day') return 'Half day · from 12:00'
  const days = bookingDays(booking)
  return `${days} ${days === 1 ? 'day' : 'days'}`
}

/** Dates as shown in lists: a single date for one-day rentals, otherwise a range. */
export function bookingDatesLabel(booking) {
  if (booking.startDate === booking.endDate) return formatBookingDate(booking.startDate)
  return `${formatBookingDate(booking.startDate)} → ${formatBookingDate(booking.endDate)}`
}

export function formatBookingDate(iso) {
  return new Intl.DateTimeFormat('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }).format(
    new Date(`${iso}T12:00:00`),
  )
}
