import { apiFetch } from './client';

export function getEquipment() {
  return apiFetch('/equipment');
}

export function createEquipment(formData) {
  return apiFetch('/equipment', {
    method: 'POST',
    body: formData,
  });
}