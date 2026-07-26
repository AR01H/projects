import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

function stripCrossorigin() {
  return {
    name: 'strip-crossorigin',
    enforce: 'post',
    transformIndexHtml(html: string) {
      return html.replace(/ crossorigin/g, '')
    },
  }
}

export default defineConfig({
  base: './',
  plugins: [react(), tailwindcss(), stripCrossorigin()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  build: {
    modulePreload: false,
  },
})
