import { apiFetch } from './client';

export function getEquipment() {
  return apiFetch('/equipment');
}