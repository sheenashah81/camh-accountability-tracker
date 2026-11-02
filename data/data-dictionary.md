# Data dictionary v0.9

Defines the content types, fields, controlled vocabularies and validation rules for the tracker. It is the input to the Drupal build (Phase 4), the CSV import (Phase 7) and the automated data checks (Phase 9). Related RTM rows: S5, S6, W3, D2e, D2g, Q4. Related requirements: FR-05, FR-07, FR-08, FR-10, FR-12, FR-13, FR-14, FR-16, FR-17, FR-19, NFR-08. Data rules come from `docs/data-approach.md`.

**Conventions.** Field names are `snake_case`. Dates use ISO 8601 (`YYYY-MM-DD`). "Req" = required to publish. Every public record carries provenance (source, licence, date) so that every figure is traceable (NFR-08). Text fields exist in English and Spanish; Spanish values display "pending human review" until marked reviewed.

## 1. Provenance block (shared by Commitment, Indicator value, Document)
| Field | Type | Req | Rule |
|---|---|---|---|
| `source_name` | Text (200) | Yes | Publisher and title, e.g. the document or dataset name |
| `source_url` | URL | Yes | Must resolve to the public source; HTTPS preferred |
| `licence` | Vocabulary (see 3.6) | Yes | Checked before the record is added (data-approach rule 5) |
| `retrieved_on` | Date | Yes for indicator values | Not in the future |
| `verified_on` | Date | Yes for commitments | Date a person confirmed the source supports the record |
| `is_illustrative` | Boolean | Yes | Default `false`. If `true`, the record shows an "Illustrative" tag on screen (FR-08) and `source_url` may be blank |

Rule P1: a record with `is_illustrative = false` must have `source_name`, `source_url`, `licence` and a verified/retrieved date.
Rule P2: a record with `is_illustrative = true` must never name a real government or institution as the author of the commitment text.

## 2. Content types

### 2.1 Country
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `iso3` | Text (3) | Yes | ISO 3166-1 alpha-3, unique, uppercase (e.g. KEN) |
| `name_en`, `name_es` | Text (100) | Yes | |
| `region` | Vocabulary 3.5 | Yes | |
| `summary` | Long text | No | Plain-language profile intro |
| `is_starter` | Boolean | Yes | True for the eight starter countries |

### 2.2 Institution
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `code` | Text (20) | Yes | Unique, `ISO3-ABBREVIATION` (e.g. KEN-MOH); used by commitments to reference the institution (added v0.3) |
| `name_en`, `name_es` | Text (200) | Yes | |
| `country` | Reference → Country | Yes | One country per institution record |
| `institution_type` | Vocabulary | Yes | Ministry, Agency, Parliament, Local authority, Partner organisation |
| `website` | URL | No | |

### 2.3 Commitment
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `title_en`, `title_es` | Text (200) | Yes | Unique within a country |
| `slug` | Text | Yes | Generated, lowercase, hyphenated; used in the friendly URL |
| `country` | Reference → Country | Yes | |
| `institution` | Reference → Institution | Yes | Institution's country must equal the commitment's country |
| `theme` | Vocabulary 3.1 (multi) | Yes | At least one |
| `commitment_type` | Vocabulary 3.4 | Yes | Policy, Law, Strategy, Pledge, Budget line |
| `description_en`, `description_es` | Long text | Yes | Plain language; 50 to 1,200 characters |
| `date_made` | Date | Yes | Date the commitment was made or adopted |
| `target_date` | Date | No | Must be on or after `date_made` |
| `status` | Vocabulary 3.2 | Yes | Set by workflow rules, see 4 |
| `status_updated_on` | Date | Yes | Auto-set when `status` changes |
| `last_updated` | Date | Yes | Auto-set on save |
| provenance block | Section 1 | Yes | |
| `workflow_state` | Vocabulary 3.3 | Yes | Draft, In review, Published, Rejected |
| `review_comment` | Long text | If rejected | Required when state is Rejected (FR-14) |
| `milestones` | Reference → Milestone (multi) | No | |
| `documents` | Reference → Document (multi) | No | |

### 2.4 Milestone
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `commitment` | Reference → Commitment | Yes | |
| `title_en`, `title_es` | Text (200) | Yes | |
| `due_date` | Date | Yes | |
| `completed_on` | Date | No | Not before the commitment's `date_made`; not in the future |
| `milestone_status` | Vocabulary 3.2 (subset) | Yes | Not started, In progress, Completed, Delayed |
| `is_illustrative` | Boolean | Yes | Milestone progress is usually illustrative unless a public report supports it |

### 2.5 Progress update
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `commitment` | Reference → Commitment | Yes | |
| `update_date` | Date | Yes | Not in the future |
| `summary_en`, `summary_es` | Long text | Yes | 20 to 800 characters |
| `new_status` | Vocabulary 3.2 | Yes | Applying a published update sets the commitment's status |
| `author` | Reference → User | Yes | Auto-set |
| `source_url` | URL | No | Supporting evidence |
| `workflow_state` | Vocabulary 3.3 | Yes | |
| `is_illustrative` | Boolean | Yes | |

### 2.6 Indicator
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `code` | Text (40) | Yes | Unique, e.g. a source's own indicator code |
| `name_en`, `name_es` | Text (200) | Yes | |
| `definition_en`, `definition_es` | Long text | Yes | Plain-language definition |
| `unit` | Text (40) | Yes | e.g. "% of adolescents", "per 100,000" |
| `theme` | Vocabulary 3.1 | Yes | |
| `direction` | Vocabulary | Yes | Higher is better / Lower is better / Neutral |
| `age_group` | Text (40) | Yes | e.g. "10 to 19 years" |

### 2.7 Indicator value (observation)
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `indicator` | Reference → Indicator | Yes | |
| `country` | Reference → Country | Yes | |
| `year` | Integer | Yes | 1990 to current year |
| `value` | Decimal | Yes | Numeric; range check against the indicator's plausible range (rule V4) |
| `sex` | Vocabulary | No | Female, Male, Total |
| `notes` | Text | No | e.g. modelled estimate, survey year |
| provenance block | Section 1 | Yes | `retrieved_on` required |

Uniqueness: one value per indicator, country, year and sex.

### 2.8 Document
| Field | Type | Req | Rule / notes |
|---|---|---|---|
| `title_en`, `title_es` | Text (200) | Yes | |
| `document_type` | Vocabulary 3.7 | Yes | |
| `country` | Reference → Country | No | Blank for global documents |
| `theme` | Vocabulary 3.1 (multi) | No | |
| `language` | Vocabulary | Yes | English, Spanish, Other |
| `year` | Integer | Yes | |
| `file` | Media (PDF or other) | One of file or external URL | PDF, max 10 MB, and only files with a licence that permits redistribution; otherwise link to the source instead |
| `external_url` | URL | One of file or external URL | |
| provenance block | Section 1 | Yes | |

## 3. Controlled vocabularies (taxonomies)

### 3.1 Theme
Policy and legislation · Financing · Services and workforce · Schools and education · Prevention and promotion · Data and monitoring · Rights and participation of young people. *(Proposed; wording to be tested in plain language.)*

### 3.2 Status
Not started · In progress · On track · Delayed · Completed · No recent update. Each status has a text label and an icon in addition to colour (matches the Status badge component).

### 3.3 Workflow state
Draft → In review → Published; Rejected returns to Draft with a comment.

### 3.4 Commitment type
Policy · Law · Strategy · Pledge · Budget line.

### 3.5 Region
East Africa · West Africa · Southern Africa · South Asia · Southeast Asia · Latin America · Middle East and North Africa · Europe. *(Author's grouping for the starter countries, not an official classification.)*

### 3.6 Licence
Open Government Licence v3 · CC BY 4.0 · CC BY-NC-SA 3.0 IGO · Public domain · Other, see note · Not yet checked. Records with "Not yet checked" cannot be published (rule P1).

### 3.7 Document type
Policy or law · Strategy or plan · Report · Dataset documentation · Guidance · Other.

## 4. Business rules and validation

| ID | Rule | Applies to | Check |
|---|---|---|---|
| V1 | Required fields present before submit for review | All types | Form validation and import |
| V2 | Provenance rules P1 and P2 hold | Commitment, Indicator value, Document | Form validation, import, automated test |
| V3 | Dates logical: `target_date` ≥ `date_made`; no future `completed_on`, `update_date`, `retrieved_on` | Commitment, Milestone, Progress update | Form validation, import |
| V4 | Indicator value within plausible range (e.g. percentages 0 to 100) | Indicator value | Import and data-quality report |
| V5 | No duplicate keys (indicator + country + year + sex; commitment title within country; ISO code) | Several | Import |
| V6 | Institution country equals commitment country | Commitment | Form validation |
| V7 | A commitment cannot be Completed while it has milestones not Completed | Commitment | Form validation (warning for Editors, error for Contributors) |
| V8 | A commitment is "No recent update" if `status_updated_on` is older than 12 months | Commitment | Scheduled job and data-quality report |
| V9 | Rejected state requires `review_comment` | Commitment, Progress update | Form validation |
| V10 | Spanish text present for every public field, or marked "pending human review" | All text fields | Data-quality report |
| V11 | Contributors can create and edit only records for their assigned country | Commitment, Milestone, Progress update | Permissions (FR-15) |
| V12 | Every change is logged: who, what, when | Commitment, Milestone, Progress update | Revision log (FR-16) |

## 5. Data-quality report (FR-19)
Lists: records missing a source or licence (P1) · commitments not updated in 12 months (V8) · values outside range (V4) · failed imports with row and reason · Spanish fields still pending review (V10).

## 5a. Draft records and working columns (added v0.2)
`data/commitments.csv` holds the first eight commitment records, one per starter country, all in the Draft workflow state. Two working columns support verification and are not Drupal fields: `verification_status` ("Pending verification", or "Passage verified by project author on [date]; licence not yet checked") and `verification_note` (what is still to be checked). While a record is Draft, `status` may be blank (not yet assessed) and `date_made` may be blank where the exact date is unconfirmed; both must be filled before the record moves to In review. A record whose licence is "Not yet checked" cannot be Published (rule P1). Where `date_made` or `target_date` holds a year placeholder, the note says so.

## 5b. Reference files (added v0.3)
`data/countries.csv`, `data/institutions.csv` and `data/vocabularies.csv` hold the reference data. `vocabularies.csv` has one row per term (columns: `vocabulary`, `term_code`, `label_en`, `label_es`, `sort_order`, `es_review_status`, `notes`). Spanish labels are AI-assisted and carry `es_review_status = Pending human review`. In `commitments.csv`, references use the country `iso3` and the institution `code`; theme values use the English label, separated by `|`. Run `python3 tests/data/check_data.py` to verify that the files agree with each other (references resolve, vocabulary terms exist, description lengths, date order, publish rules).

## 5c. Indicator files (added v0.4)
`data/indicators.csv` holds the indicator definitions and `data/indicator_values.csv` the observations, retrieved from the World Bank API on 2026-10-26 under CC BY 4.0. Two indicators are loaded: suicide mortality rate (SH.STA.SUIC.P5, 2000 to 2021) and population ages 0-14 (SP.POP.0014.TO, 2000 to 2025), for all eight starter countries. The population series is stored in whole people; the World Bank web page displays it in thousands. Rule V4 is applied by `tests/data/check_data.py` (suicide rate between 0 and 100 per 100,000; population above zero).

## 5d. How the Drupal build implements this dictionary (added v0.5, build pass 1)
Script: `web/scripts/build_content_model_1.php`. Exported configuration: `web/config/sync/`.

| Dictionary item | Drupal implementation | Why |
|---|---|---|
| Paired `_en` and `_es` fields | One field, translated with Drupal core content translation (English default, Spanish translation). The Commitment `description`, Country `summary` and `review_comment` fields and all titles and vocabulary term names are translatable | Standard Drupal multilingual practice; gives language URL prefixes (`/en`, `/es`) and per-language editing. Import files still carry `_es` columns, which the import maps to the Spanish translation |
| `workflow_state` | Content Moderation workflow "Commitment review": Draft, In review, Published, Rejected. The default Draft-to-Published shortcut is removed so nothing publishes without review | Built-in revisions, permissions per transition and an audit trail |
| `last_updated` | The node's own "changed" date | Set automatically on save |
| `slug` | Drupal URL alias (Pathauto, added in pass 2) | |
| Spanish review flag (V10) | Commitment field `es_review_status` (Pending human review or Reviewed by a person) | Keeps the "pending human review" label honest |
| Vocabulary `workflow_state` in `vocabularies.csv` | Not created as a list; replaced by the workflow above | |
| Vocabularies | Eight lists created from `data/vocabularies.csv`, each term with a stable `term_code` and a Spanish translation | |

Fields that are optional while a record is a Draft (`date_made`, `status`, `source_url`) are required only at the submit-for-review step; that validation, uniqueness checks (rule V5), the institution-country check (V6) and the other business rules are added in build pass 2. Milestone, Progress update, Indicator, Indicator value and Document types are also pass 2.


### Build pass 2 (added v0.6)
- **All eight content types** now exist in Drupal (Country, Institution, Commitment, Milestone, Progress update, Indicator, Indicator value, Document). Documents use a Drupal Media reference (type Document).
- **Validation** is implemented in the custom module `camh_tracker` (`web/web/modules/custom/camh_tracker`): P1; V1 (before review: date made, status, source name, and for real records source URL and verified-on date); V3; V4; V5; V6; V7 (blocks contributors only); V9; V11. V8 (no recent update), V10 (Spanish pending) and V12 (revision log) are handled by scheduled reports and Drupal's revision log in later passes.
- **Roles:** Contributor (creates and edits own commitments, milestones and progress updates, and can submit for review), Editor (edits all content and can reject or publish), Administrator (standard role). A user's assigned country is the user field `assigned_country`.
- **Working columns** `verification_status` and `verification_note` are kept as internal commitment fields so the verification trail is not lost on import.
- **Loaded data:** 8 countries, 8 institutions, 9 commitments (all Draft), 2 indicators, 384 indicator values, imported by `web/scripts/build_content_model_3.php` through the validation rules.
- **Friendly URLs:** commitments use the `slug` column (for example `/commitments/colombia-ley-2460-2025-salud-mental`); the other types use Pathauto patterns. Language prefixes `/en` and `/es`.
- **Record titles for indicator values** are generated as `CODE | ISO3 | year | sex`.
- **Smoke test:** `tests/drupal/validation_smoke_test.php`.

### Commitment page and history files (added v0.7)
`data/milestones.csv` and `data/progress_updates.csv` hold the real milestone and progress records, each pointing to a commitment by its `slug`. Only records supported by a public document are included (the UK support-team coverage target and the June 2026 progress figures); records added only to demonstrate workflows would be flagged illustrative. The public commitment page (`web/scripts/build_commitment_page.php`) shows: notices (illustrative, no recent update, Spanish pending review), country, institution, type, themes, status and its date, dates, description, progress updates, milestones, and a source and licence panel. Internal verification columns, the review comment and the Spanish review flag are not shown publicly.

### Spanish interface and content (added v0.8)
- Drupal's official community Spanish translation covers core strings. The tracker's own labels are in `web/web/modules/custom/camh_tracker/translations/es.po` and `web/scripts/translate_to_spanish.php` (views, menu items, field labels, footer, units, date style "10 de junio de 2026"). All are **AI-assisted and pending human review**.
- Commitment titles and descriptions, the milestone title and the progress summary have Spanish text in the CSV files (`title_es`, `description_es`, `summary_es`) and are created as translations in the same moderation state as the original. A Spanish version can never be public while its original is unpublished (test in `tests/drupal/validation_smoke_test.php`).
- Source names, institution names and document titles stay in the language of the source. The Spanish pages show the notice "The Spanish text is AI-assisted and pending human review" until a person marks a record as reviewed.

### Overview page (added v0.9)
The Overview (`/en`, `/es`) is computed only from published records: countries tracked, published commitments, records in preparation (unpublished commitments), indicator data points and their year range, a status breakdown of published commitments, the five latest published progress updates, and a map coloured by whether a country has a published commitment. Spanish text for the page is AI-assisted and pending human review.

## 6. Import file format (preview, finalised in Phase 7)
CSV, UTF-8, one file per content type, header row using the field names above, multi-value fields separated by `|`, references by `iso3` or `code`. Each import produces a log with counts of created, updated, skipped and failed rows, and a downloadable error file.

## Open items
- Confirm licences for the candidate sources (WHO Mental Health Atlas, IHME GBD, UNICEF Data, World Bank) before any indicator values are added.
- Decide which indicators to include (shortlist drafted in Phase 3 task 3.2).
- Identify a public commitment document per starter country (task 3.3).
- Review theme and status wording in the plain-language check.
