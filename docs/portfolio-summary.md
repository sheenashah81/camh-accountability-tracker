# Portfolio summary: Child and Adolescent Mental Health Accountability Tracker

An independent portfolio project, inspired by a public Terms of Reference for an MHPSS design and development role. It is not affiliated with, endorsed by or produced for UNICEF or WHO, and it makes no claim of production readiness.

## In one paragraph
I designed and built, end to end, a public tracker of government commitments on child and adolescent mental health: a Figma design system and clickable prototype, a Drupal 11 site with a moderated editorial workflow, bilingual pages (English and Spanish), accessible charts and a map, data import and export, and an automated test pipeline that rebuilds the whole site from scratch on every push. Every commitment links to a public source with a recorded licence check. Where something is simulated or unfinished, the project says so.

## What it shows
| Skill | Evidence |
|---|---|
| **Design systems and prototyping (Figma)** | Colour and text styles, button, status badge, text input and link components, desktop, tablet and phone layouts, a 13-link clickable prototype, a sitemap and three user journeys. Handover notes map each design decision to the build (`design/handover.md`). |
| **Drupal development and site building** | Eight content types, eight controlled vocabularies, a Draft, In review, Published, Rejected workflow, a custom validation module, a Bootstrap 5 sub-theme, and exported configuration that rebuilds the site (`docs/architecture.md`). |
| **Data and integrations** | Real countries and indicators, each with a citation, source link, licence check and retrieved-on date. CSV import with row-level error checks, a read-only JSON:API, and CSV and JSON downloads. DevInfo and DHIS2 integration is designed and documented, not connected (`docs/integrations-and-power-bi.md`). |
| **Accessibility** | Status badges with icons and text, data tables behind every chart, reduced-motion support, keyboard focus checks, and tables that stack as cards on phones. An axe-core check passes across 63 page, language and size combinations. Lighthouse (2026-11-01) scored accessibility 100, best practices 100 and SEO 100. |
| **Multilingual and right-to-left** | English and Spanish interfaces and content, plus a right-to-left layout test. The Spanish is AI-assisted and has had a first review by a person; it has not been reviewed by a native or professional Spanish speaker, which would be required before real-world use. |
| **Testing and delivery** | Data checks, 11 validation tests, 28 Behat user journeys and the accessibility check run in GitHub Actions on a clean machine. Twelve defects found by testing are logged with their fixes (`evidence/bug-log.md`). |
| **Documentation and handover** | Requirements traceability matrix, architecture, admin guide, editor training guide, hosting plan, maintenance log and a roadmap of open items. |

## Decisions worth a conversation
- **Real, cited data.** A commitment is included only where a public document supports it. Anything added to demonstrate admin workflows is labelled illustrative. Progress statuses come only from dated, official sources.
- **Drafts stay private.** Only the UK record is published, and a test proves that Drafts never appear in downloads or in the Spanish version of a page.
- **Evidence over optimism.** Where evidence was partial the status says so, and where none existed the status is left blank.
- **A map that works everywhere.** I dropped a WebGL map for an ECharts vector map with a table alternative, so it works on every device.
- **Rebuild from scratch in CI.** A defect that only appeared on a clean build (B-010) is the reason the pipeline starts from nothing every time.

## What is real, simulated and next
| Built and tested | Simulated by design | Next |
|---|---|---|
| Design file and prototype, site, workflow, validation, pages, charts, map, downloads, bilingual interface, CSV import, tests, CI | DevInfo and DHIS2 integration (designed, not connected); the admin review queue screen (designed; the build uses the Drupal content list) | Native-speaker Spanish review; evidence-based status for the Colombia record; a hosted demo (`docs/hosting.md`) |

## Where to look
| To see | Open |
|---|---|
| The story in detail | `docs/case-study.md` |
| Screenshots of the built site | `evidence/theme/`, `evidence/accessibility/` |
| Design handover and Figma pages | `design/handover.md`, `design/figma-links.md` |
| Requirement-by-requirement status | `docs/rtm.md` |
| How the checks run | `docs/ci.md` |
| Open items and roadmap | `docs/open-items.md` |
