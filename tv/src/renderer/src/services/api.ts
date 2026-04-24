import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1';
const DEVICE_ID = import.meta.env.VITE_DEVICE_ID || 'TV-RECEP-01';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'X-Device-ID': DEVICE_ID,
    'X-Device-Type': 'tv'
  }
});

export const fetchConfig = async () => {
  const response = await api.get('/tv/config');
  return response.data;
};

export const createEventSource = () => {
  // EventSource doesn't support custom headers natively in browser API (only fetch does)
  // However, we can pass it in the URL as a query param and adapt our middleware to read it,
  // OR we can use a library like @microsoft/fetch-event-source
  // Since we control the local network, we can pass it as a query param.
  const url = `${API_URL}/tv/stream?device_id=${DEVICE_ID}&device_type=tv`;
  return new EventSource(url);
};

export default api;
