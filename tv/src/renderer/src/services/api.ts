import axios from 'axios';

let API_URL = localStorage.getItem('sgsa_api_url') || import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1';
let DEVICE_ID = localStorage.getItem('sgsa_device_id') || import.meta.env.VITE_DEVICE_ID || '';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'X-Device-ID': DEVICE_ID,
    'X-Device-Type': 'tv'
  }
});

export const updateApiConfig = (url: string, deviceId: string) => {
  API_URL = url;
  DEVICE_ID = deviceId;
  localStorage.setItem('sgsa_api_url', url);
  localStorage.setItem('sgsa_device_id', deviceId);
  api.defaults.baseURL = url;
  api.defaults.headers['X-Device-ID'] = deviceId;
  document.title = `SGSA TV: ${deviceId}`;
};

export const fetchConfig = async () => {
  const response = await api.get('/tv/config');
  return response.data;
};

export const createEventSource = () => {
  const url = `${API_URL}/tv/stream?device_id=${DEVICE_ID}&device_type=tv`;
  return new EventSource(url);
};

export default api;
