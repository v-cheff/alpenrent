import { apiFetch } from './client.js'

// Renter: bookings the current user made
export function getMyBookings() {
  return apiFetch('/bookings')
}

export function createBooking(data) {
  return apiFetch('/bookings', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  })
}

export function cancelBooking(id) {
  return apiFetch(`/bookings/${id}`, {
    method: 'DELETE',
  })
}

// Owner: bookings renters made for the owner's equipment
export function getIncomingBookings() {
  return apiFetch('/my/bookings')
}
