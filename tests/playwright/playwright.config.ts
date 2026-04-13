import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  timeout: 60000,
  use: {
    baseURL: process.env.ELGG_BASE_URL || 'http://elgg',
    ignoreHTTPSErrors: true,
  },
  workers: 1,
  projects: [{ name: 'chromium', use: { browserName: 'chromium' } }],
});
