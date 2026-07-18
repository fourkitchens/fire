# Playwright VRT

Visual Regression Testing for this project, set up with [Fire](https://github.com/fourkitchens/fire) and powered by [`@fkbender/playwright-vrt-scripts`](https://github.com/fourkitchens/playwright-vrt-scripts).

VRT works by capturing screenshots of your site from two environments — a **baseline** (typically the live Pantheon environment) and a **candidate** (your local environment) — and comparing them pixel by pixel. Failures mean something visually changed.

---

## Prerequisites

- Node (version pinned in `.nvmrc`)
- Playwright browsers installed (`npx playwright install --with-deps` — done automatically by `fire vrt:playwright:init`)
- A populated `.env` file (see below)

### `.env`

This file is gitignored and must exist locally. It is generated automatically by `fire vrt:playwright:init`, but can be created manually if needed:

```
BASELINE_URL="https://live-yoursite.pantheonsite.io"
CANDIDATE_URL="https://yoursite.ddev.site"
BASELINE_TERMINUS_ENV=live
BASELINE_TERMINUS_SITE=yoursite
```

---

## Running VRT Locally

```bash
# Full two-pass comparison: captures baseline from BASELINE_URL, candidate from CANDIDATE_URL
npm run vrt:local

# CI-style run (used in automated pipelines)
npm run vrt:ci
```

Results open automatically in your browser as an HTML report. Failing tests show a diff of baseline vs. candidate.

---

## Adding Pages to Test

Open `tests/data/vrtCommonPages.json` and add an entry:

```json
[
  {
    "name": "Home",
    "path": "/",
    "screenshotName": "home-page"
  },
  {
    "name": "Login",
    "path": "/user/login",
    "screenshotName": "user-login"
  },
  {
    "name": "News listing",
    "path": "/news",
    "screenshotName": "news-listing"
  }
]
```

Each entry requires three fields:

| Field | Purpose |
|---|---|
| `name` | Human-readable label shown in the test report |
| `path` | Site-relative URL to visit (must start with `/`) |
| `screenshotName` | Base filename for saved screenshots (no extension, no spaces) |

The spec automatically generates tests for every page × every device profile (Desktop, iPad, iPhone 12), so adding one entry produces three new tests.

---

## Device Profiles

Tests run against three device profiles, all using Chromium:

| Profile | Viewport |
|---|---|
| Desktop Chrome | 1280 × 720 |
| iPad (gen 7) | 810 × 1080 |
| iPhone 12 | 390 × 844 |

Device profiles are defined in `tests/support/4k_utilities.js`. Firefox and WebKit are available but commented out in `playwright.config.js` — remove the comments to enable them.

---

## Running a Single Test or Page

```bash
# Run only tests matching a page name
npx playwright test --grep "Home"

# Run only desktop tests
npx playwright test --grep "desktop"

# Run all VRT tests (tagged @vrt)
npx playwright test --grep "@vrt"
```

---

## Viewing the Report

After a run, open the HTML report:

```bash
npx playwright show-report
```

On CI, reports are uploaded as artifacts. Each failing test shows the baseline screenshot, the candidate screenshot, and a pixel-diff image.

---

## Troubleshooting

**Tests fail with image differences I didn't expect**

- Check that `stage_file_proxy` is enabled so images load correctly in your local environment.
- Make sure your local database is synced from a recent pull of the live environment.
- Confirm there are no pending CSS or JS builds that differ from what's on live.

**`networkidle` timeout errors**

The test waits for the page to become network-idle before screenshotting. If a page has long-polling or persistent background requests, this will time out. Add a custom wait or increase the timeout for that specific test.

**Snapshots are stale after a design change**

Once a visual change is intentional and approved, update the baseline by re-running the VRT capture against the new live environment.

**`.env` file not found**

Run `fire vrt:playwright:init` to regenerate it, or copy the values from a teammate or the project's password manager entry.
