import * as aftUtilities from '../support/4k_utilities'
import commonPages from '../data/vrtCommonPages.json'

const { test, expect } = require('@playwright/test');

//test.describe.configure({ mode: 'serial' });

const runCommonVrtTest = async ({ page, path, screenshotName }) => {

  await page.goto(path);
  // Force-load lazy images.
  await aftUtilities.forceLoadLazyImages(page);
  await page.waitForLoadState('networkidle');

  await page.addStyleTag({
    content: `
      * {
        animation: none !important;
        transition: none !important;
      }
    `
  });

  await expect(page).toHaveScreenshot(screenshotName, { fullPage: true });
}

const deviceProfiles = [
  {
    title: 'Desktop',
    profile: 'desktopChrome',
    screenshotSuffix: 'desktop',
  },
  {
    title: 'iPad',
    profile: 'iPad',
    screenshotSuffix: 'ipad',
  },
  {
    title: 'iPhone 12',
    profile: 'iPhone12',
    screenshotSuffix: 'iphone-12',
  },
]

for (const device of deviceProfiles) {
  test.describe(`Common VRT - ${device.title}`, () => {
    test.use({ ...aftUtilities.getVrtDeviceProfile(device.profile) });

    for (const commonPage of commonPages) {
      test(`Common VRT - ${commonPage.name} - ${device.screenshotSuffix} @vrt`, async ({ page }) => {
        await runCommonVrtTest({
          page,
          path: commonPage.path,
          screenshotName: `${commonPage.screenshotName}-${device.screenshotSuffix}.png`,
        });
      });
    }
  });
}
