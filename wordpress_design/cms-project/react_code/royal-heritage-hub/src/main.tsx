import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { BrowserRouter } from 'react-router-dom'
import { ErrorBoundary } from './ErrorBoundary'
import './theme/theme.css'
import { applyTheme } from './theme/applyTheme'
import App from './App.tsx'

applyTheme()

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ErrorBoundary>
      <BrowserRouter basename="/">
        <App />
      </BrowserRouter>
    </ErrorBoundary>
  </StrictMode>,
)
