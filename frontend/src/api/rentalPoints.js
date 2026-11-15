import { apiFetch } from './client.js'

const jsonHeaders = { 'Content-Type': 'application/json' }

export function getMyRentalPoints() {
  return apiFetch('/my/rental-points')
}

export function createRentalPoint(data) {
  return apiFetch('/my/rental-points', {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify(data),
  })
}

export function updateRentalPoint(id, data) {
  return apiFetch(`/my/rental-points/${id}`, {
    method: 'PUT',
    headers: jsonHeaders,
    body: JSON.stringify(data),
  })
}

export function deleteRentalPoint(id) {
  return apiFetch(`/my/rental-points/${id}`, {
    method: 'DELETE',
  })
}
