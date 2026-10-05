/// <reference types="vitest/config" />

import angular from '@analogjs/vite-plugin-angular';
import {fileURLToPath} from 'node:url';
import {defineConfig} from 'vitest/config';

export default defineConfig({
  plugins: [angular()],
  resolve: {
    // Mirrors `baseUrl: "./"` in tsconfig: source imports may start with `src/`.
    alias: [{find: /^src\//, replacement: fileURLToPath(new URL('./src/', import.meta.url))}],
  },
  test: {
    environment: 'jsdom',
    globals: true,
    include: ['src/**/*.spec.ts'],
    setupFiles: ['src/test-setup.ts'],
  },
});
