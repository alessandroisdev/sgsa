import axios from 'axios';

let API_URL = localStorage.getItem('sgsa_api_url') || import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1';
let DEVICE_ID = localStorage.getItem('sgsa_device_id') || import.meta.env.VITE_DEVICE_ID || '';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'X-Device-ID': DEVICE_ID,
    'X-Device-Type': 'totem'
  }
});

export const updateApiConfig = (url: string, deviceId: string) => {
  API_URL = url;
  DEVICE_ID = deviceId;
  localStorage.setItem('sgsa_api_url', url);
  localStorage.setItem('sgsa_device_id', deviceId);
  api.defaults.baseURL = url;
  api.defaults.headers['X-Device-ID'] = deviceId;
  document.title = `SGSA Totem: ${deviceId}`;
};

export const fetchConfig = async () => {
  const response = await api.get('/totem/config');
  return response.data;
};

export const generateTicket = async (serviceId: string, priorityId: string) => {
  const response = await api.post('/totem/ticket', {
    service_id: serviceId,
    priority_id: priorityId
  });
  return response.data;
};

export default api;
