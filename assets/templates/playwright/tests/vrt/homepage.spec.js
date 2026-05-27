import * as atkCommands from '../support/atk_commands'
import * as aftUtilities from '../support/aft_utilities'

const { test, expect } = require('@playwright/test');

//test.describe.configure({ mode: 'serial' });

const runHomepageVrtTest = async ({ page, screenshotName }) => {

  await page.goto('/');
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

test.describe('Homepage VRT - Desktop', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('desktopChrome') });

  test('Homepage VRT - desktop @vrt', async ({ page }) => {
    await runHomepageVrtTest({
      page,
      screenshotName: 'homepage-desktop.png',
    });
  });
});

test.describe('Homepage VRT - iPad', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('iPad') });

  test('Homepage VRT - ipad @vrt', async ({ page }) => {
    await runHomepageVrtTest({
      page,
      screenshotName: 'homepage-ipad.png',
    });
  });
});

test.describe('Homepage VRT - iPhone 12', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('iPhone12') });

  test('Homepage VRT - iphone @vrt', async ({ page }) => {
    await runHomepageVrtTest({
      page,
      screenshotName: 'homepage-iphone-12.png',
    });
  });
});
