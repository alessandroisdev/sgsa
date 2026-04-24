import './assets/scss/app.scss'

import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App'

document.title = `SGSA Totem: ${import.meta.env.VITE_DEVICE_ID || 'Não configurado'}`

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>
)
