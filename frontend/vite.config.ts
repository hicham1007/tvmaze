import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { loadEnv } from 'vite'
import { defineConfig } from 'vitest/config'

export default defineConfig(({ mode }) => {
  // Reads .env files and the real environment; '' = also variables without the VITE_ prefix.
  const env = loadEnv(mode, process.cwd(), '')

  return {
    plugins: [react(), tailwindcss()],
    server: {
      // The browser always talks to its own origin; Vite forwards /api to Laravel.
      // In Docker the same job is done by the reverse proxy (see ../proxy).
      proxy: {
        '/api': env.API_PROXY_TARGET || 'http://localhost:8000',
      },
    },
    test: {
      environment: 'jsdom',
      setupFiles: './src/test/setup.ts',
      restoreMocks: true,
    },
  }
})
