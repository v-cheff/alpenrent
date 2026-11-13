import { apiFetch } from './client';

export function getEquipment() {
  return apiFetch('/equipment');
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
