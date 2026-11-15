import { apiFetch } from './client';

export function registerUser(data) {
  return apiFetch('/auth/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });
}

export function loginUser(data) {
  return apiFetch('/auth/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(data),
  });
}

export function getCurrentUser() {
  return apiFetch('/auth/me');
}

export function logoutUser() {
  return apiFetch('/auth/logout', {
    method: 'POST',
  });
}
