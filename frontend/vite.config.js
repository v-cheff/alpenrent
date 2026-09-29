import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  plugins: [vue()],
  server: {
    port: 5173,
    host: true, // Слушает 0.0.0.0 (доступен снаружи контейнера)
    watch: {
      usePolling: true, // Гарантирует Hot Reload внутри Docker на всех ОС
    },
  },
})