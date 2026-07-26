import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { HashRouter } from 'react-router-dom'
import { HelmetProvider } from 'react-helmet-async'
import { ErrorBoundary } from './ErrorBoundary'
import './theme/theme.css'
import './theme/animation.css'
import './theme/element-animations.css'
import { applyTheme } from './theme/applyTheme'
import App from './App.tsx'

applyTheme()

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <ErrorBoundary>
      <HelmetProvider>
        <HashRouter>
          <App />
        </HashRouter>
      </HelmetProvider>
    </ErrorBoundary>
  </StrictMode>,
)
