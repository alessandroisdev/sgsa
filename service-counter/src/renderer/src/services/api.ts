import axios from 'axios';

let API_URL = localStorage.getItem('sgsa_api_url') || import.meta.env.VITE_API_URL || 'http://localhost:8084/api/v1';

const api = axios.create({
  baseURL: API_URL,
});

export const updateApiConfig = (url: string) => {
  API_URL = url;
  localStorage.setItem('sgsa_api_url', url);
  api.defaults.baseURL = url;
};

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('sgsa_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export const auth = {
  login: async (credentials: any) => {
    const res = await api.post('/auth/login', credentials);
    if (res.data.token) {
      localStorage.setItem('sgsa_token', res.data.token);
      localStorage.setItem('sgsa_user', JSON.stringify(res.data.user));
    }
    return res.data;
  },
  logout: async () => {
    await api.post('/auth/logout');
    localStorage.removeItem('sgsa_token');
    localStorage.removeItem('sgsa_user');
  },
  getUser: () => {
    const user = localStorage.getItem('sgsa_user');
    return user ? JSON.parse(user) : null;
  }
};

export const attendant = {
  getCounters: async () => {
    const res = await api.get('/counters');
    return res.data;
  },
  getState: async (counterId: string) => {
    const res = await api.get(`/counters/${counterId}/state`);
    return res.data;
  },
  callNext: async (counterId: string) => {
    const res = await api.post(`/counters/${counterId}/call-next`);
    return res.data;
  },
  recall: async (ticketId: string) => {
    const res = await api.post(`/queue/${ticketId}/recall`);
    return res.data;
  },
  start: async (ticketId: string) => {
    const res = await api.post(`/queue/${ticketId}/start`);
    return res.data;
  },
  finish: async (ticketId: string) => {
    const res = await api.post(`/queue/${ticketId}/finish`);
    return res.data;
  },
  absent: async (ticketId: string) => {
    const res = await api.post(`/queue/${ticketId}/absent`);
    return res.data;
  }
};

export default api;
