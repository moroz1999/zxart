/// <reference types="vitest/config" />

import angular from '@analogjs/vite-plugin-angular';
import {defineConfig} from 'vitest/config';

export default defineConfig({
  plugins: [angular()],
  test: {
    environment: 'jsdom',
    globals: true,
    include: ['src/**/*.spec.ts'],
    setupFiles: ['src/test-setup.ts'],
  },
});
