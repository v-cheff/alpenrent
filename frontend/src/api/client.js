const API_URL = import.meta.env.VITE_API_URL;

export async function apiFetch(path, options = {}) {
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');
  const response = await fetch(`${API_URL}${path}`, { ...options, headers });

  if (!response.ok) {
    let message = `HTTP error: ${response.status}`;

    try {
      const body = await response.json();
      const validationMessage = Object.values(body.errors || {}).flat().join(' ');
      message = validationMessage || body.message || message;
    } catch {
      // Keep the HTTP status message when the server doesn't return JSON.
    }

    throw new Error(message);
  }

  return response.json();
}