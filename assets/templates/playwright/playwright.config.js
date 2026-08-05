// @ts-check
const { defineConfig, devices } = require('@playwright/test');

require('dotenv').config();

// CANDIDATE_URL comes from .env (written by fire vrt:playwright:init).
// REMOTE_ENV_BASE_URL is set at runtime by the @fkbender/playwright-vrt-scripts
// when running comparisons against a remote environment.
// __DEFAULT_BASE_URL__ is replaced by fire during init as a last-resort fallback.
const configuredBaseURL = (process.env.CANDIDATE_URL || process.env.REMOTE_ENV_BASE_URL || '__DEFAULT_BASE_URL__')
  .trim()
  .replace(/\/+$/, '')

/**
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  timeout: 60000,
  testDir: './tests',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: process.env.CI ? 1 : undefined,
  reporter: process.env.CI ? 'blob' : 'html',

  expect: {
    timeout: 10000,
    toHaveScreenshot: {
      stylePath: './css/screenshotGlobalStyle.css'
    }
  },

  use: {
    baseURL: `${configuredBaseURL}/`,
    trace: 'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        userAgent: 'fire-playwright-vrt/1.0'
      },
    },

    // {
    //   name: 'firefox',
    //   use: { ...devices['Desktop Firefox'] },
    // },

    // {
    //   name: 'webkit',
    //   use: { ...devices['Desktop Safari'] },
    // },
  ],
});
