import { apiFetch } from './client';

/**
 * Public catalog. Optional filters: { rentalPointId, startDate, endDate }.
 * With dates each item includes `availableSizes` for that period.
 */
export function getEquipment(filters = {}) {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      params.set(key, value);
    }
  });

  const query = params.toString();
  return apiFetch(`/equipment${query ? `?${query}` : ''}`);
}

export function getMyEquipment() {
  return apiFetch('/my/equipment');
}

export function getEquipmentItem(id) {
  return apiFetch(`/equipment/${id}`);
}

export function createEquipment(formData) {
  return apiFetch('/equipment', {
    method: 'POST',
    body: formData,
  });
}

export function updateEquipment(id, formData) {
  return apiFetch(`/equipment/${id}`, {
    method: 'POST',
    body: formData,
  });
}

export function deleteEquipment(id) {
  return apiFetch(`/equipment/${id}`, {
    method: 'DELETE',
  });
}
