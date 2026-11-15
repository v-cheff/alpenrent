import { apiFetch } from './client.js'

export function createBooking(data) {
  return apiFetch('/bookings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })
}
