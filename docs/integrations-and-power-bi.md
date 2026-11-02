# Integrations and the Power BI comparison

An independent portfolio prototype. Nothing here connects to UNICEF, WHO, DevInfo, DHIS2 or Microsoft production systems. This document describes how the tracker is designed to exchange data and where Power BI fits. The mechanisms in the first section are built and tested; the connector designs in the later sections are documentation only.

## 1. What the tracker exposes today
| Interface | Address | Content | Access |
|---|---|---|---|
| CSV downloads | `/tracker/data/commitments.csv`, `/tracker/data/indicator-values.csv` | Published records only, with source, licence and date columns, in the page language | Public |
| JSON downloads | `/tracker/data/commitments.json`, `/tracker/data/indicator-values.json` | Same content as the CSV files | Public |
| JSON:API | `/jsonapi` | Read-only access to published content | Public, read only |
| Admin CSV import | `/admin/content/import-commitments` | Checks a commitments file row by row and loads valid rows as Drafts | Editors and administrators |
| Admin CSV import (indicator values) | `/admin/content/import-indicator-values` | Checks an indicator-values file row by row and loads valid rows unpublished for review | Editors and administrators |

Draft, in-review and rejected records never appear in the downloads or the API; a test checks this on every push.

## 2. Why the dashboard is a custom page and not a Power BI report
The Terms of Reference allow "Power BI or a similar tool". The tracker's dashboard uses ECharts (Apache-2.0) inside the Drupal site, reading the same published data.

| Question | Power BI | Custom dashboard in Drupal (chosen) |
|---|---|---|
| Public web access | Publishing for anonymous viewers needs Power BI Embedded or Fabric capacity (paid). "Publish to web" makes a report public but is unsuitable for anything sensitive and gives little control | Public by design; Drafts are held back by the review workflow |
| Cost and licensing | Per-user licences for authors and, for sharing, Pro, Premium or capacity | No licence fees; open-source libraries |
| Accessibility | Supports keyboard use and screen readers but has limited control over markup, focus order and text alternatives | Full control; every chart has a data table, automated axe-core checks run on every push |
| Languages and right-to-left | Report-by-report translation effort | Interface and content translation handled by Drupal, with a right-to-left layout test |
| Rebuild and test | A report file is a binary artifact; automated testing is limited | Rebuilt from configuration and CSV on a clean machine on every push |
| Authoring by analysts | Strong: drag-and-drop modelling and DAX, quick prototypes | Requires front-end development |
| Platform | Power BI Desktop runs on Windows only; the service runs in a browser | Any platform |

**Where Power BI is the better choice:** internal analysis, ad hoc exploration and reports for a known audience of licensed users. Choosing it for the public tracker would trade accessibility control and rebuildability for faster authoring.

## 3. Using Power BI with the tracker's data (design, not built)
1. In Power BI Desktop choose **Get data, Web** and enter the address of a CSV or JSON download. For data that refreshes on a schedule, publish to the Power BI service and use a gateway or a cloud-reachable address.
2. The CSV files carry the source name, source URL, licence and date on every row, so any report built from them can show provenance.
3. Use the `language` column to filter to one language, and `is_illustrative` to exclude demonstration records.
4. Credentials: the downloads are public and need none. If a private feed were added, it would use a read-only account and token, never an administrator login.

## 4. DHIS2 (design, not built)
DHIS2 is an open-source health information platform with a documented Web API and a public demo instance. The planned pattern is a one-way, read-only import:
1. A scheduled job requests aggregate indicator values from the DHIS2 Web API for agreed indicators and periods.
2. Rows are mapped to the `indicator_values` format (indicator code, country, year, value, unit, source, licence, retrieved-on date) and written to a CSV.
3. The existing checker (`tests/data/check_data.py`) validates the file, and the admin import path loads it as Drafts for review.
4. Source, licence and retrieval date are recorded for every value, as for the World Bank and WHO data already loaded.
Open questions for a real deployment: which instance and organisation units, authentication method, and the licence of the specific dataset.

## 5. DevInfo (documentation only)
DevInfo is a database system used by development agencies to hold and present indicator data. No access to a DevInfo instance is assumed or claimed. If one were available, the tracker would consume an export (CSV or SDMX) through the same validate-then-import path as above, with the instance's licence and metadata recorded per dataset.

## 6. Security principles for any integration
- Read-only by default; no write-back to external systems.
- Least privilege: a dedicated account per source, scoped to the data needed.
- Secrets kept in environment configuration, never in the repository.
- Every imported value passes validation and review before it is public.
- Provenance (source, licence, retrieved-on date) travels with each value.

## 7. Claims this project does not make
- No connection to any production system has been made or tested.
- No claim is made about the licence of any DHIS2 or DevInfo dataset; each would be checked as the World Bank and WHO data were (`data/licence-check.md`).
