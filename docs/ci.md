# Continuous integration

Workflow: `.github/workflows/ci.yml` (GitHub Actions, the current equivalent of the Travis CI and Jenkins named in the Terms of Reference). It runs on every push to `main` and on every pull request. Results: https://github.com/sheenashah81/camh-accountability-tracker/actions

| Job | What it does | Time |
|---|---|---|
| Data checks | Runs `tests/data/check_data.py`: references between the CSV files resolve, vocabulary terms exist, dates are in order, descriptions have the right length, a record with an unchecked licence cannot be Published | about 5 s |
| Site build, validation tests and accessibility | Starts DDEV on a clean virtual machine, installs Drupal, imports the exported configuration (`web/config/sync`), loads the fixed lists and the cited data, then runs `tests/drupal/validation_smoke_test.php` (11 checks, including "no Spanish version is public while its original is unpublished") and the Behat user journeys in `tests/behat` (31 scenarios), and `tests/accessibility/axe_check.py` (axe-core, WCAG 2.2 AA, keyboard focus, phone width, 63 page, language and size combinations in English, Spanish and the right-to-left test) | about 10 min (the accessibility check covers 63 page, language and size combinations) |

A step that fails turns the run red and GitHub emails the repository owner. The accessibility report is kept as a build artifact.

## What the pipeline proves
Anyone can rebuild the whole site from the repository alone: the exported configuration, the scripts in `web/scripts/` and the CSV files in `data/`. That is the handover requirement, checked on every change.

## Rebuild order used by the pipeline
1. `drush site:install standard`
2. `scripts/ci_prepare.php` (aligns the identifiers of standard configuration; creates the footer block)
3. `drush config:import`
4. `drush locale:update` and the project's Spanish strings
5. `scripts/load_vocabulary_terms.php`, `build_content_model_3.php`, `build_commitment_page.php`, `translate_to_spanish.php`, then `normalise_aliases.php` (last, so every web address comes from the English title)
6. The validation tests, the Behat journeys, then the accessibility check

## Limits
- Not run on branches without a pull request.
- The workflow itself cannot be run on a laptop; changes to it are tested by pushing.
- The accessibility check is automated rules plus a keyboard pass: it does not replace screen reader and zoom testing.
- Last verified green on 2026-10-29 (run 40865567604); earlier runs fixed web addresses generated from Spanish names and Chrome starting slowly.
- Failures were checked locally (the data checker rejects a broken institution reference); a red run on GitHub has not yet been observed.
- GitHub warns that Node.js 20 actions are deprecated; versions should be bumped when newer ones are released.

## Bugs found while building the pipeline
- DDEV was started in the repository root instead of `web/`.
- A fresh Drupal site gives standard configuration new random identifiers, so the import tried to delete them: fixed in `ci_prepare.php`.
- The terms of the fixed lists are content, not configuration, so a clean site needs `load_vocabulary_terms.php`.
