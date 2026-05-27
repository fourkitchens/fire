import * as atkCommands from '../support/atk_commands'
import * as aftUtilities from '../support/aft_utilities'


const { test, expect } = require('@playwright/test');

//test.describe.configure({ mode: 'serial' });

const runComponentsVrtTest = async ({ page, context, screenshotName }) => {
  await atkCommands.logInViaUli(page, context, 1)
  await page.goto('/vrt-components-test-dont-delete');
  // Force-load lazy images.
  await aftUtilities.forceLoadLazyImages(page);
  await aftUtilities.normalizeJoinTheMovementSignup(page);
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

test.describe('Components page VRT - Desktop', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('desktopChrome') });

  test('Components page VRT @vrt', async ({ page, context }) => {
    await runComponentsVrtTest({
      page,
      context,
      screenshotName: 'components-desktop.png',
    });
  });
});

test.describe('Components page VRT - iPad', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('iPad') });

  test('Components page VRT @vrt', async ({ page, context }) => {
    await runComponentsVrtTest({
      page,
      context,
      screenshotName: 'components-ipad.png',
    });
  });
});

test.describe('Components page VRT - iPhone 12', () => {
  test.use({ ...aftUtilities.getVrtDeviceProfile('iPhone12') });

  test('Components page VRT @vrt', async ({ page, context }) => {
    await runComponentsVrtTest({
      page,
      context,
      screenshotName: 'components-iphone-12.png',
    });
  });
});
