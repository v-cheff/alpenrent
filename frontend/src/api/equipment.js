import { getEquipment } from './api';

export function getEquipment() {
  return apiFetch('/equipment');
}