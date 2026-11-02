# Lighthouse results

Run on 2026-11-01 against the local site with Lighthouse 12 in headless Chromium (Chromium 131), mobile profile (simulated mid-range phone, throttled network) and desktop profile. Scores are out of 100; the server is a local development container, so performance figures show the page weight and rendering work and not a production host. The full JSON reports (about 9 MB) are not stored in the repository; the commands below regenerate them.

| Page | Profile | Performance | Accessibility | Best practices | SEO | LCP | CLS | TBT |
|---|---|---|---|---|---|---|---|---|
| Overview | mobile | 91 | 100 | 100 | 100 | 2.8 s | 0.004 | 70 ms |
| Overview | desktop | 98 | 100 | 100 | 100 | 0.9 s | 0.021 | 0 ms |
| Commitments list | mobile | 94 | 100 | 100 | 100 | 2.5 s | 0.002 | 60 ms |
| Commitments list | desktop | 99 | 100 | 100 | 100 | 0.8 s | 0.005 | 0 ms |
| Commitment detail (UK) | mobile | 92 | 100 | 100 | 100 | 2.7 s | 0.003 | 0 ms |
| Commitment detail (UK) | desktop | 100 | 100 | 100 | 100 | 0.6 s | 0.02 | 0 ms |
| Country profile (Kenya) | mobile | 98 | 100 | 100 | 100 | 2.0 s | 0.001 | 30 ms |
| Country profile (Kenya) | desktop | 99 | 100 | 100 | 100 | 0.8 s | 0.004 | 0 ms |
| Indicators | mobile | 98 | 100 | 100 | 100 | 2.0 s | 0.023 | 40 ms |
| Indicators | desktop | 99 | 100 | 100 | 100 | 0.7 s | 0.002 | 0 ms |
| Data | mobile | 97 | 100 | 100 | 100 | 2.1 s | 0.035 | 0 ms |
| Data | desktop | 100 | 100 | 100 | 100 | 0.4 s | 0.01 | 0 ms |
| Overview (Spanish) | mobile | 91 | 100 | 100 | 100 | 2.8 s | 0.027 | 100 ms |
| Overview (Spanish) | desktop | 98 | 100 | 100 | 100 | 0.9 s | 0.009 | 0 ms |

## Findings and fixes

- **Missing meta description (SEO 91 on every page):** fixed. Public pages now carry a meta description, and commitment pages use the opening of the record's own description. SEO is 100 on every page. The Spanish pages show the English default text until its translation is added.
- **Layout shift on the Indicators page (mobile CLS 0.17, above the 0.1 guideline):** fixed. Each chart's height is now set by the server, so the table below no longer jumps when the chart draws. CLS is now 0.023.
- **Accessibility 100 and best practices 100 on all 14 runs.** This agrees with the axe-core checks (63 combinations, no violations).
- **Source maps (informational, no score impact):** the bundled third-party libraries (Bootstrap, ECharts) are served minified without source maps. This does not affect users and is left as is.
- **Mobile performance 91 to 98:** the lowest scores are the Overview pages, where the map and chart libraries load. Largest contentful paint is 2.0 to 2.8 s on the throttled phone profile. A production host with compression and caching would improve this; lazy-loading the map is a possible refinement.

## Re-running

Lighthouse needs a Chromium browser. The runs above used a browser installed inside the DDEV web container (`npm i lighthouse playwright-core`, then `npx playwright-core install chromium`) and `npx lighthouse http://localhost/<path>` with `--chrome-flags="--headless=new --no-sandbox"`, once per page and profile (add `--preset=desktop` for desktop).
