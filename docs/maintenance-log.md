# Maintenance log and runbook

## Running the site on a Mac (DDEV)
Needs Docker Desktop (open and running) and DDEV. From the repository's `web/` folder:
| Task | Command |
|---|---|
| Start | `ddev start` |
| Stop | `ddev stop` |
| One-time login link | `ddev drush uli` |
| Clear caches | `ddev drush cr` |
| Install packages | `ddev composer install` |

If the site cannot be reached, Docker or the project is probably stopped: run `ddev start`.

## Configuration changes
Site configuration lives in `web/config/sync` and is tracked in Git. After changing configuration in the admin, run `ddev drush config:export -y` and commit. To apply configuration from Git run `ddev drush config:import -y`.

## Rebuild from scratch
The order used by CI is in `docs/ci.md`: install a blank site, run `web/scripts/ci_prepare.php`, import configuration, fetch Spanish translations, load vocabulary terms and data, then the tests.

## Tests
| Check | Command |
|---|---|
| CSV files | `python3 tests/data/check_data.py` |
| Validation and publication rules | `ddev drush php:script /var/www/tests/drupal/validation_smoke_test.php` (from `web/`) |
| User journeys (Behat, 34 scenarios) | `ddev exec vendor/bin/behat --config /var/www/tests/behat/behat.yml` (from `web/`) |
| Accessibility and phone layout | `.venv/bin/python tests/accessibility/axe_check.py` (needs Chrome; see the script header) |
CI runs all three on every push to `main`.

## Web addresses
Saving a Spanish translation can make the address generator rebuild an address from the Spanish name. `web/scripts/normalise_aliases.php` (run last, safe to repeat) restores one address per record from the English title or the CSV slug.

## Mirrored stylesheet (right-to-left)
`web/web/themes/custom/camh_theme/css/style.rtl.css` is generated from `css/style.css`. After changing `style.css`, regenerate it from `web/`: `ddev exec "cd /var/www/html/web/themes/custom/camh_theme && npx --yes rtlcss css/style.css css/style.rtl.css"`.

## Updating
Drupal core, modules and the theme are managed by Composer: `ddev composer update`, then run the tests and export configuration. Security releases of Drupal should be applied promptly. Bootstrap, ECharts and Noto Sans are bundled locally; check their release notes occasionally.

## Log
| Date | Change |
|---|---|
| 2026-10-28 | Drupal 11.4 site built with DDEV; content model, review workflow, validation module, roles; data loaded from CSV |
| 2026-10-28 | Bootstrap 5 theme; Overview, Commitments, detail, Country, Indicators and Data pages; charts and map |
| 2026-10-28 | English and Spanish; publication guard for translations (bug B-001) |
| 2026-10-28 | Accessibility and phone checks; GitHub Actions CI |
| 2026-10-28 | Full site title, clearer map, language redirect, header and skip-link fixes, status badge overlap fix |
| 2026-10-28 | Behat journeys added (visitor, privacy, editor); Commitments list wireframe built in Figma (not yet checked by screenshot) |
| 2026-10-28 | Right-to-left layout test added (Arabic test language at /ar, mirrored stylesheet, banner) |
