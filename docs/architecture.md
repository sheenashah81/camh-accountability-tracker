# Architecture

An independent portfolio prototype. It is not affiliated with UNICEF, WHO, DevInfo or DHIS2 and makes no claim of production readiness.

## What it is
A Drupal 11 website that holds cited public commitments on child and adolescent mental health and cited public indicators, shows them in English and Spanish, and lets authorised people add and review records.

```
 data/*.csv  ──(web/scripts)──▶  Drupal 11 (DDEV)  ──▶  public pages and downloads
 cited sources                    content types, review workflow,      Overview · Commitments · Indicators
 + licences                       validation module, roles             Countries · Data · JSON:API (read-only)
                                           │
                                 web/config/sync (exported configuration, in Git)
 GitHub Actions: rebuilds the site from config + CSV and runs the tests on every push
```

## Layers
| Layer | What | Where |
|---|---|---|
| Content model | Eight content types (Country, Institution, Commitment, Milestone, Progress update, Indicator, Indicator value, Document), eight fixed lists, a review workflow (Draft, In review, Published, Rejected) | `data/data-dictionary.md`; `web/config/sync`; `web/scripts/build_content_model_*.php` |
| Rules | Validation of the data dictionary rules (uniqueness, same-country institution, dates, provenance, required fields before review, contributor country) and the publication guard for translations | `web/web/modules/custom/camh_tracker` |
| Pages | Overview, Commitments list (filters, chips, table or map), commitment detail, country profile, Indicators, Data | module controllers, Views, `web/scripts/build_*.php` |
| Charts and map | ECharts 5 (local, Apache-2.0): line charts per indicator, bar charts per indicator, a vector map with a table alternative | module `js/` and `libraries/` |
| Theme | Bootstrap 5 based theme `camh_theme` with local assets, Noto Sans (SIL OFL) self-hosted, design tokens from `design/design-tokens.md` | `web/web/themes/custom/camh_theme` |
| Languages | English default, Spanish via Drupal core translation; address prefixes /en and /es; Arabic exists only as a right-to-left layout test at /ar (hidden from visitors, English text, mirrored stylesheet generated with rtlcss); official Spanish pack plus AI-assisted tracker strings | `translations/es.po`, `web/scripts/translate_to_spanish.php` |
| Quality | Data checker, validation tests, axe-core accessibility check (42 page, language and size combinations), CI | `tests/`, `.github/workflows/ci.yml`, `docs/ci.md` |

## Data flow
1. A person finds a public document, confirms the passage and its licence, and records them in `data/*.csv` (source, licence, verified-on date).
2. `tests/data/check_data.py` checks the files against each other.
3. Scripts load the records into Drupal as Drafts through the validation rules. A person reviews and publishes them through the workflow.
4. Only published records appear on public pages, in downloads and in the read-only API.

## Real, simulated and roadmap
| Real (built and tested) | Simulated or documentation only | Roadmap |
|---|---|---|
| Site, content model, workflow, validation, pages, charts, map, bilingual interface, downloads, CI | DevInfo and DHIS2 integration; Power BI comparison | Production hosting, import form for content types other than commitments, high-fidelity Figma screens |

## Decisions that differ from the first plan
| Plan | What was built | Why |
|---|---|---|
| Paired English and Spanish fields | Drupal core content translation | Standard multilingual practice; gives language prefixes and per-language editing |
| MapLibre map | ECharts vector map | MapLibre needs WebGL, which not every device has; the vector map works everywhere and is lighter |
| Four indicators or more | Two (suicide mortality rate, child population) | WHO mental health workforce, spending and policy data had gaps for the starter countries |
| Theme built with Sass | CSS variables over compiled Bootstrap | No build step to maintain; same tokens |

## Security and privacy notes
- No real personal data is stored. The only account is a local administrator for development.
- The JSON:API is read-only.
- Secrets and local settings (`settings.ddev.php`, uploaded files, `.env`) are git-ignored.
- Unreviewed records and their translations are never public; a test checks this on every push.
