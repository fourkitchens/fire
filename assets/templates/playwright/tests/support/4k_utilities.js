const { devices } = require('@playwright/test')
// Helper function for lazy loading images.
const forceLoadLazyImages = async (page) => {
  await page.evaluate(async () => {
    document.querySelectorAll('[decoding="async"]').forEach((element) => {
      element.decoding = 'sync'
    })

    const images = Array.from(document.querySelectorAll('img[loading="lazy"]'))

    for (const image of images) {
      image.loading = 'eager'
      image.removeAttribute('loading')

      try {
        await image.decode()
      } catch (error) {
        // Ignore decode errors for lazy images.
      }
    }
  })
}

// Returns devices profiles for VRT.
const vrtDeviceProfiles = (() => {
  const desktopChromeProfile = { ...devices['Desktop Chrome'] }
  const iPadProfile = { ...devices['iPad (gen 7)'] }
  const iPhone12Profile = { ...devices['iPhone 12'] }

  delete desktopChromeProfile.defaultBrowserType
  delete iPadProfile.defaultBrowserType
  delete iPhone12Profile.defaultBrowserType

  return {
    desktopChrome: desktopChromeProfile,
    iPad: iPadProfile,
    iPhone12: iPhone12Profile,
  }
})()

const getVrtDeviceProfile = (key) => vrtDeviceProfiles[key]

export {
  forceLoadLazyImages,
  getVrtDeviceProfile,
}
