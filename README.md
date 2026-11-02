# Child & Adolescent Mental Health Accountability Tracker: Portfolio Replica

[![CI](https://github.com/sheenashah81/camh-accountability-tracker/actions/workflows/ci.yml/badge.svg)](https://github.com/sheenashah81/camh-accountability-tracker/actions/workflows/ci.yml)

> **Independent portfolio project.** Inspired by a public consultancy Terms of Reference for an MHPSS design and development role. It is **not** affiliated with, endorsed by, or produced for UNICEF or WHO. Indicators come from **publicly available sources, cited per record**; commitments are included only where a public document supports them, and any other demonstration records are **labelled illustrative**. No official logos, assets, credentials or confidential information are used. Visual styling is an independent approximation of publicly visible conventions.

## What this is
A portfolio project covering the full workflow of designing and developing a public accountability tracker: Figma design, a Drupal 11 website and admin backend, an interactive dashboard, data import and validation, accessibility, multilingual support, automated testing and documentation.

## Read this first
The [case study](docs/case-study.md) summarises the problem, what was built, the decisions made, what testing found, and what is real and simulated, and the roadmap.

## Run it
Requires Docker and [DDEV](https://ddev.com). The DDEV project lives in `web/`. The exact sequence for a clean rebuild (start DDEV, `composer install`, install a blank Drupal site, import `web/config/sync`, load the Spanish strings and the CSV data with the scripts in `web/scripts`) is the workflow in `.github/workflows/ci.yml`, explained in `docs/ci.md`. The site then runs at https://camh-tracker.ddev.site.

Tests, from the repository root unless stated: `python3 tests/data/check_data.py` (data); from `web/`, `ddev exec vendor/bin/behat --config /var/www/tests/behat/behat.yml` (user journeys); `tests/accessibility/axe_check.py` (accessibility, see `tests/accessibility/requirements.txt`).

## Status
A working prototype: Drupal 11 site with a content model, review workflow and validation rules; public Overview, Commitments, commitment detail, Country, Indicators and Data pages with charts and a map; admin CSV import forms for commitments and indicator values; English and Spanish (Spanish is AI-assisted and was reviewed by a person on 2026-11-01); automated data, validation, user-journey (34 scenarios) and accessibility checks on every push (`docs/ci.md`). Start with `docs/requirements.md`; see `docs/rtm.md` for requirement-by-requirement status and honest limitations.

## Repository structure
| Folder | Purpose |
|---|---|
| `docs/` | Requirements, traceability matrix, architecture, guides, maintenance log |
| `design/` | Figma links, exports, design tokens, handover spec |
| `data/` | Public datasets (cited), illustrative records and data dictionary |
| `web/` | Drupal project, configuration, custom theme and module |
| `dashboard/` | Dashboard front-end source |
| `tests/` | Behat, PHPUnit and accessibility tests |
| `evidence/` | Screenshots, reports, bug log |

## Real vs simulated
Maintained in `docs/rtm.md` (column "Demo level"). Nothing here connects to UNICEF, WHO, DevInfo or DHIS2 production systems.
