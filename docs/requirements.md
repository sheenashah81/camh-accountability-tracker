# Functional and non-functional requirements

Derived from the TOR (explicit) and from `docs/audiences.md` (assumed, unvalidated). Each requirement links to an RTM row. **Priority:** Must / Should / Could (my proposal, to be reviewed). **Source:** TOR = stated in the Terms of Reference; ASSUMED = derived from my audience assumptions.

## Functional requirements
| ID | The system shall… | Audience | Source | RTM | Priority |
|---|---|---|---|---|---|
| FR-01 | Show an overview page with key indicators and progress summaries across countries | Public, donors | TOR | S3, D2b | Must |
| FR-02 | Display commitments on a map, with a table alternative | Public | TOR | S4, D2b | Must |
| FR-03 | Let users filter commitments by country, region, theme, status and year | Public, donors | TOR | S4 | Must |
| FR-04 | Let users search commitments by keyword | Public | ASSUMED | S4 | Should |
| FR-05 | Show a commitment detail page: description, responsible institution, milestones, status, timeline, progress, sources | Public | TOR | S3 | Must |
| FR-06 | Show charts of progress and indicators, each with a text or table alternative | Public | TOR | S4, W2 | Must |
| FR-07 | Show the source, licence and last-updated date for every indicator and commitment | Public | ASSUMED | D2g | Must |
| FR-08 | Mark illustrative records clearly on screen | Public | ASSUMED (project rule) | D2g | Must |
| FR-09 | Offer downloads of public data as CSV and JSON | Donors | ASSUMED | S5 | Should |
| FR-10 | Provide a repository of PDF and other documents, searchable and filterable | Public | TOR | W3 | Must |
| FR-11 | Provide static informational pages (about, methodology, accessibility, contact) | Public | TOR | W4 | Must |
| FR-12 | Let authorised contributors log in and create or edit commitments, milestones, progress updates and documents | Contributors | TOR | S6 | Must |
| FR-13 | Validate submitted records and show clear error messages | Contributors | TOR | D2g | Must |
| FR-14 | Support a workflow: draft → in review → published, with comments on rejection | Contributors, admins | ASSUMED | S6 | Should |
| FR-15 | Limit contributors to their own ministry's records; allow UN reviewers to see all ministries in their country | Contributors, admins | ASSUMED | S6 | Should |
| FR-16 | Keep a history of changes (who, what, when) | Admins | ASSUMED | S6 | Should |
| FR-17 | Import data from CSV and from a simulated API feed, with an import log and error report | Admins | TOR | S5, D2e | Must |
| FR-18 | Refresh imported data on a schedule | Admins | TOR | D2f | Should |
| FR-19 | Provide a data-quality report (missing sources, stale updates, failed validations) | Admins | TOR | D2g | Should |
| FR-20 | Manage users and roles (Contributor, Editor, Administrator) | Admins | TOR | S6 | Must |
| FR-21 | Switch language between English and Spanish with friendly URLs (e.g. /en/…, /es/…) | Public | TOR | W6, Q12 | Must |
| FR-22 | Display correctly in a right-to-left layout (tested with placeholder text) | Public | TOR | Q12 | Should |
| FR-23 | Show meaningful empty, loading and error states | Public | TOR | S3 | Must |
| FR-24 | Provide plain-language summaries and shareable page links | Youth | ASSUMED | D2d | Could |
| FR-25 | Send page-view events to Google Analytics only after consent | Public | TOR | W7 | Should |

## Non-functional requirements
| ID | Requirement | Source | RTM | Priority |
|---|---|---|---|---|
| NFR-01 | Meet WCAG 2.1 AA: keyboard navigation, visible focus, contrast, semantic structure, labelled forms, chart alternatives | TOR | W2, Q11 | Must |
| NFR-02 | Responsive at desktop, tablet and mobile widths with no horizontal scroll | TOR | D2c, Q9 | Must |
| NFR-03 | Work well on slow mobile networks (test on throttled connection; target set in test plan) | ASSUMED | D2c | Should |
| NFR-04 | SEO-friendly: unique titles, meta descriptions, headings, sitemap, canonical and language tags | TOR | W5 | Must |
| NFR-05 | Follow UNICEF-inspired styling conventions using only public references, labelled an independent approximation | TOR | W1, S2 | Must |
| NFR-06 | Security: login for contributors only, role-based permissions, HTTPS, input validation, no secrets in the repo, regular updates | TOR ("secure") | S3, S5 | Must |
| NFR-07 | Privacy: no personal data of public users beyond consented analytics; privacy notice | ASSUMED | W7 | Must |
| NFR-08 | Data integrity: every figure traceable to a cited source; automated consistency checks | TOR | D2g | Must |
| NFR-09 | Reproducible: a fresh clone can be set up from the documentation | TOR (handover) | D3c | Must |
| NFR-10 | Maintainability: Git history, config exported to files, documented architecture and runbook | TOR | D3d, Q18 | Must |
| NFR-11 | Automated tests run in continuous integration | TOR | Q19, Q20 | Should |
| NFR-12 | Browser support: current Chrome, Safari, Firefox, Edge; iOS Safari and Android Chrome | ASSUMED | D2h | Should |

## Out of scope (stated honestly)
Production hosting, real DevInfo/DHIS2 production connection, single sign-on, UNICEF approval, official design assets, and anything needing real personal data.

## Open items
Priorities and the performance target are my proposals and need your review. Real stakeholder priorities are unknown.
