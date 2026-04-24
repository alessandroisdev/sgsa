import './assets/scss/app.scss'

import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App'

document.title = 'SGSA Guichê'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>
)
