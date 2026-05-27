// @ts-check
const { defineConfig, devices } = require('@playwright/test');
import path from 'path'

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
require('dotenv').config();

const configuredBaseURL = (process.env.REMOTE_ENV_BASE_URL || '__DEFAULT_BASE_URL__')
  .trim()
  .replace(/\/+$/, '')

/**
 * @see https://playwright.dev/docs/test-configuration
 */
module.exports = defineConfig({
  // Timeout.
  timeout: 100000,
  testDir: './tests',
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 2 : 0,
  /* Opt out of parallel tests on CI. */
  workers: process.env.CI ? 1 : undefined,
  /* Reporter to use. See https://playwright.dev/docs/test-reporters */
  reporter: process.env.CI ? 'blob' : 'html',

  expect: {
    // timeout per assertion.
    timeout: 100000,
    toHaveScreenshot: {
      stylePath: './css/screenshotGlobalStyle.css'
    }
  },

  /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL: `${configuredBaseURL}/`,

    /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
    trace: 'on',
    launchOptions: {
      slowMo: 0
    }
  },
  /* Configure projects for major browsers */
  projects: [
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        userAgent: 'aft-main-playwright-ci/1.0'
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
