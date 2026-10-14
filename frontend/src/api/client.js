const API_URL = 'http://127.0.0.1:8000/v1';

export async function apiFetch(path, options = {}) {
  const response = await fetch(`${API_URL}${path}`, options);

  if (!response.ok) {
    throw new Error(`HTTP error: ${response.status}`);
  }

  return response.json();
}