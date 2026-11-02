# Requirements Traceability Matrix (RTM)

Source: Terms of Reference "MHPSS Design & Development Consultant" (TMC0007210). TOR wording is preserved in the "TOR wording" column (explicit requirements). "Planned work" is my proposed implementation choice, not a TOR requirement.

**Demo level:** Real (built and working locally) · Simulated (mock) · Doc-only (written, not built) · Not demonstrable (needs external access) · Not claimable
**Status:** Planned → In progress → Built → Verified

## A. Scope of Work
| ID | TOR wording | Planned work | Demo level | Status | Test evidence | Limitations |
|---|---|---|---|---|---|---|
| S1 | Produce wireframes in Figma to establish the design, layout, and overall look and feel | Figma file, Phases 1–2 | Real | In progress | | |
| S2 | Visually appealing, user-friendly, intuitive design that complies with UNICEF standards | UNICEF-inspired tokens from public conventions, labelled approximation | Real (approx.) | Planned | | Official standards not accessible |
| S3 | Develop the tracker dashboard using Power BI or a similar tool; visually appealing, interactive, responsive, secure, web-based, accessible, multiple audiences | Custom JS dashboard inside Drupal; Power BI comparison written (docs/integrations-and-power-bi.md) | Real | In progress | Overview page at /en and /es (KPI tiles, map, progress summary, recent updates) built from published records; country profile charts; commitment detail; evidence/theme screenshots | Table or map toggle, filter chips, Region and Year filters, an indicators comparison page and downloads not yet built |
| S4 | Interactive visual components: charts, maps, filters, tables; users customise and explore data | ECharts + MapLibre + accessible tables | Real | In progress | ECharts line charts on country profiles (JSON:API, data tables as text alternative); ECharts vector map on the Overview with a table alternative; filters and search on the Commitments list | MapLibre was replaced by an ECharts SVG map because WebGL is not available everywhere; map is not zoomable; map-and-table toggle on the list not built |
| S5 | Secure integration with existing datasets and APIs (e.g., DevInfo, Power BI, or DHIS2) | Drupal JSON:API, CSV import, simulated feed, DHIS2 public demo read (designed in docs/integrations-and-power-bi.md) | Simulated / Real (DHIS2 demo) | In progress | Data page with CSV and JSON downloads (commitments, indicator values) with source, licence and date columns; read-only JSON:API; CSV import with validation (data/*.csv and web/scripts; admin form for commitments at /admin/content/import-commitments, Behat-tested); tests/drupal/validation_smoke_test.php checks drafts are never exported | DevInfo and DHIS2 integration is not built (simulated or documentation only); the admin import form covers commitments only |
| S6 | Administrative backend enabling authorized users to update data dynamically | Drupal admin, roles, editorial workflow | Real | In progress | Drupal 11 content model built 2026-10-27 (web/scripts/build_content_model_*.php, web/config/sync); roles Contributor and Editor created; review workflow Draft, In review, Published, Rejected; tests/drupal/validation_smoke_test.php (10 checks pass) | Contributor country restriction (V11) implemented in validation but not yet tested with a contributor account; admin guide and screen recording pending |
| S7 | End-user training and comprehensive technical documentation | Docs set and training material | Real | In progress | docs/editor-training-guide.md (90-minute session with exercises); docs/admin-guide.md, architecture.md, hosting.md | Slide deck and video not produced |
| S8 | Accountability prototype and subsequent technical support as primary delivery priority: launch readiness, troubleshooting, optimization, sustainability | Launch checklist, maintenance log | Real (docs) | Planned | | |
| S9 | Broader MHPSS design and development support: user-centered design, technical advisory, platform enhancements, maintenance, performance monitoring, resolution of operational issues | One v1.1 improvement cycle, performance report | Real (small) | Planned | | |

## B. Site standards
| ID | TOR wording | Planned work | Demo level | Status | Test evidence | Limitations |
|---|---|---|---|---|---|---|
| W1 | Utilize existing styles, conventions, components of global UNICEF.org: fonts, colors, branding, global navigation | Bootstrap 5 sub-theme with approximated tokens, no official assets | Real (approx.) | Planned | | Official components not demonstrable |
| W2 | Follow UNICEF Accessibility Standards; at minimum WCAG 2.1 AA | axe, Lighthouse (run 2026-11-01, evidence/lighthouse), Pa11y, keyboard and VoiceOver tests, chart alternatives | Real | In progress | Automated check tests/accessibility/axe_check.py (axe-core 4.10.2, WCAG 2.0 to 2.2 A and AA, 16 page and viewport combinations in English and Spanish: 0 violations; no horizontal scroll at 375 px; keyboard Tab pass: every stop has a visible focus indicator; skip link and landmarks present); report in evidence/accessibility | Automated rules cover only part of WCAG. VoiceOver, 200 and 400 percent zoom, reduced motion, chart colour contrast, right-to-left layout and a Pa11y or Lighthouse run in CI are still to do. UNICEF standard text not accessible: WCAG 2.1 AA used |
| W3 | Host PDF or other document file versions in a dynamic database | Drupal Media + Document content type + Views | Real | In progress | Document content type and Document media type created (web/scripts/build_content_model_2.php); no files loaded | Only files whose licence permits redistribution will be hosted; others are links |
| W4 | Other page content will be static content | Basic pages | Real | Planned | | |
| W5 | In line with UNICEF SEO standards | Titles, metadata, sitemap, canonical, hreflang | Real (generic) | Planned | | UNICEF SEO standard not available |
| W6 | Support friendly multilingual URLs | Language prefix + Pathauto | Real | In progress | Spanish added; /en and /es prefixes; aliases work in both; official Spanish pack plus AI-assisted strings (module es.po, translate_to_spanish.php); commitments translated; bug B-001 fixed; RTL layout test at /ar with mirrored stylesheet, test banner, hidden from the language switcher, in the accessibility check (63 combinations clean) | Spanish is AI-assisted and was reviewed by a person on 2026-11-01 (new text stays pending until checked); right-to-left tested as a layout only (English text mirrored at /ar, no Arabic content or translation); charts and the map are not mirrored |
| W7 | Support tracking via Google Analytics | GA4 test property with consent gate | Simulated | Planned | | Not production analytics |

## C. Deliverables
| ID | TOR wording | TOR deadline | Planned work | Demo level | Status | Test evidence | Limitations |
|---|---|---|---|---|---|---|---|
| D1a | Figma wireframes covering main dashboard views, navigation, user journeys, filters, visualizations and key interaction points | 30 Nov 2026 (10 days) | Phases 1–2 | Real | In progress | evidence/figma/2026-10-23-wireframe-home-desktop-lofi.png, evidence/figma/2026-10-26-wireframe-home-mobile-lofi.png, evidence/figma/2026-10-26-wireframes-with-components.png | Home / Overview at desktop and mobile; remaining screens pending; Commitments list specified (docs/wireframe-specs/commitments-list.md), Figma build pending |
| D1b | Agreed information architecture, layout and user experience specifications | same | IA doc, sitemap, spec | Real / Simulated | In progress | docs/information-architecture.md, docs/user-journeys.md, docs/requirements.md, docs/wireframe-specs/commitments-list.md | No client to agree with |
| D1c | Design aligned with UNICEF branding, accessibility and usability standards | same | Contrast checks, small usability test | Real (approx.) | Planned | | |
| D2a | Fully developed dashboard in Power BI or an agreed equivalent platform | 30 Jan 2027 (30 days) | Phases 5–6 | Real | Planned | | |
| D2b | Interactive visualization of key indicators: charts, maps, tables, filters and other components | same | Phase 6 | In progress | Overview page built (key indicators, map, progress summary, recent updates) | Only one commitment is published, so summaries are thin by design: no unreviewed records are shown |  |
| D2c | Responsive interface optimized for different screen sizes and user needs | same | Desktop, tablet, mobile | Real | Planned | | |
| D2d | User-friendly navigation and accessible presentation for multiple audiences | same | Public, contributor, admin views | In progress | Phone layout checked at 375 px: tables become stacked cards; screenshots in evidence/accessibility | Contributor and admin views not yet reviewed on a phone |  |
| D2e | Secure integration with relevant existing datasets, databases and/or APIs (e.g., DevInfo, Power BI, DHIS2) | same | See S5 | Simulated + Doc-only | Planned | | |
| D2f | Documented data flows and data-refresh mechanisms | same | Diagrams, cron/import | Real (local) | Planned | | |
| D2g | Data validation and quality checks for accuracy and consistency of dashboard outputs | same | Validation rules + automated tests | In progress | Rules V1 to V12 and P1 implemented in web/web/modules/custom/camh_tracker; tests/drupal/validation_smoke_test.php (10 checks pass); tests/data/check_data.py for the CSV files | V2 (illustrative records must not name a real institution as author) is a data rule not yet enforced in code; data-quality report pending |  |
| D2h | Testing and bug-fixing report, including issues identified and resolved | same | Phase 9 | Real | Planned | | |
| D2i | Final dashboard validated and approved by UNICEF | same | Self-review and peer feedback | Not demonstrable | Planned | | No UNICEF approval |
| D3a | End-user training and comprehensive technical documentation | 14 Nov 2027 (10 days) | Docs, training slides/video | Real | In progress | Written guides in docs/; training guide with exercises | Slides and video not produced |
| D3b | Production-ready dashboard deployed in the agreed UNICEF environment in advance of the launch event | same | Portfolio deployment only | Not demonstrable | Planned | | Never claim production readiness |
| D3c | Complete handover of source/configuration files, design files, documentation and relevant credentials/access information | same | Git repo, Drupal config export, Figma file, documented access process | Real (no real credentials) | Planned | | |
| D3d | Maintenance and troubleshooting guidance | same | Runbook | Real | Planned | | |
| D3e | Accountability prototype technical support and troubleshooting following launch | same | Simulated feedback log | Simulated | Planned | | |
| D3f | Resolution of bugs and performance issues identified by users, with minor refinements based on stakeholder feedback | same | Fix 3+ logged issues | Real | Planned | | |
| D3g | Support for data refresh processes, dashboard maintenance, final technical handover and sustainability recommendations | same | Docs | Real / Doc-only | Planned | | |
| D4a | Updated design/configuration files, maintenance records and technical recommendations for supported MHPSS products | 14 Nov 2027 (30 days) | Maintenance log, recommendations note | Real (small) | Planned | | |
| D4b | Work sequenced so the accountability prototype and subsequent support remain the explicit priority | same | Roadmap | Doc-only | Planned | | |
| D4c | Design and technical support for agreed broader MHPSS digital and non-digital products, training materials, donor communication & reporting across UNICEF MH team & IASC MHPSS RG | same | One sample donor-style report from synthetic data | Simulated / Doc-only | Planned | | |

## D. Qualifications and experience (what the portfolio can evidence)
| ID | TOR wording | Portfolio evidence | Demo level | Status |
|---|---|---|---|---|
| Q1 | Visual design sensibility that is clean, simple, and sharp, with ability to edit as needed | Figma hi-fi | Real | Planned |
| Q2 | Systematic, disciplined and analytical approach to problem solving, close attention to detail | RTM, tests, bug log | Real | Planned |
| Q3 | Absorb, rapidly, new technical information as required | Decision log, documentation set | Real | In progress |
| Q4 | Design and functional requirements gathering | Requirements doc (assumptions labelled) | Real | Built |
| Q5 | Development of information architecture and UX design | Sitemap, journeys | Real | Built |
| Q6 | Atomic design and component-based theme approach | Figma atoms to pages (button, status badge, text input, header, footer components); Twig components | Real | In progress |
| Q7 | Designing for Drupal (latest versions) responsive websites using bootstrap | Drupal 11 + Bootstrap 5 | Real | In progress | Drupal 11 with Bootstrap 5 theme camh_theme; responsive layout via Bootstrap grid; screenshots in evidence/theme | CSS override approach, no Sass build; mobile layout and accessibility audit still to do |
| Q8 | Design and Drupal theme development using bootstrap base theme | Bootstrap sub-theme | Real | In progress | web/web/themes/custom/camh_theme (copy of the Bootstrap 5 theme with local assets and design tokens) | CSS override approach, no Sass build; mobile layout and accessibility audit still to do |
| Q9 | Responsive theming for desktop and mobile on Drupal | Breakpoint tests | Real | Planned |
| Q10 | Theme planning and architecture based on existing development | Extend contrib base theme; decision log | Real | Planned |
| Q11 | Web accessibility standards (WCAG AA) | Accessibility report | Real | Planned |
| Q12 | Multilingual theming (RTL) | EN + AR (RTL) + FR or ES | Real | Planned |
| Q13 | Functional and technical requirements gathering | As Q4 | Real | Built |
| Q14 | Implementing Drupal 9 and Drupal 8 websites | Cannot be claimed; Drupal 11 used, concepts transfer | Not claimable | n/a |
| Q15 | Extending or creating contributed Drupal modules | Small custom module + configured contrib | Real (small) | Planned |
| Q16 | Multilingual sites with additional complexity | RTL, URL prefixes, translated taxonomy | Real (limited) | Planned |
| Q17 | Agile methodology and software dev tools (Jira, VSTS, GitHub) | GitHub Issues/Project board | Real | Planned |
| Q18 | Git-based repositories | This repository | Real | In progress |
| Q19 | Automated testing (QA) using Behat | Behat + Drupal Extension | Real (small) | Verified | tests/behat: 34 scenarios, 193 steps pass (visitor journeys in English and Spanish, privacy of drafts including Spanish versions and the API, editor access); a deliberately wrong expectation was shown to fail; runs in CI | Public and editor journeys only; contributor creation journey with the assigned-country restriction not yet scripted; browser (JavaScript) behaviour such as the Table or Map toggle is covered by the accessibility check, not Behat |
| Q20 | Continuous integration (e.g., Travis CI and/or Jenkins) | GitHub Actions (modern equivalent) | Real | Verified | GitHub Actions run 40684893180 passed on a clean machine (site rebuilt from config and CSV, 11 validation tests, 20 accessibility checks); docs/ci.md | Runs on main and pull requests only; a red run on GitHub not yet observed; Behat tests and the Pa11y or Lighthouse run not yet added |
| Q21 | Drupal as a service solution (Acquia, Pantheon, etc.) | Dev-tier trial or comparison write-up | Real or Doc-only | Planned |
| Q22 | Bachelors in Computer Science, Data Analytics, User Design, graphic/product design or related, or relevant experience | CV + MSc + this portfolio | n/a | n/a |
| Des1 | Experience working with UNICEF, WHO or another UN entity | CV only | n/a | n/a |
| Des2 | Experience supporting a global accountability initiative or public-facing evidence/data platform | This project | Real | Planned |
| Des3 | Working knowledge of French, Spanish or Arabic | Only languages the author can review | n/a | Open question |
