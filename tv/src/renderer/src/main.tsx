import './assets/scss/app.scss'

import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App'

document.title = `SGSA TV: ${import.meta.env.VITE_DEVICE_ID || 'Não configurada'}`

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>
)
