import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1';
const DEVICE_ID = import.meta.env.VITE_DEVICE_ID || 'TOTEM-RECEP-01';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'X-Device-ID': DEVICE_ID,
    'X-Device-Type': 'totem'
  }
});

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
