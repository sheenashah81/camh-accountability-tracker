# Design handover

Text for the "08 Handover" page of the Figma file. It describes what the design file contains, how it relates to the built site, and what is simulated. Independent portfolio prototype; not affiliated with UNICEF or WHO.

## Purpose
The Figma file records the design decisions behind the CAMH accountability tracker: the tokens, the components, the low-fidelity wireframes and reference captures of the implemented site. The built Drupal theme (`web/web/themes/custom/camh_theme`) is the source of truth for the final interface; the file shows how it was designed and why.

## Pages in the file
| Page | Contents | Kind |
|---|---|---|
| Foundations | 12 colour styles and 5 text styles, with swatches and specimens | Editable styles |
| 03 Wireframes low-fi | Home / Overview (desktop, mobile); Commitments list (desktop, no results, mobile); Commitment detail; Country profile; Contributor form; Admin review queue (desktop); open mobile menu | Editable layers |
| 04 Hi-fi desktop | Overview, Commitments list, Commitment detail, Country profile (Kenya), Indicators at 1280 px; Overview, Commitment detail and Country profile in Spanish | Images of the built site |
| 05 Hi-fi tablet mobile | The same five pages at 768 px and 375 px | Images of the built site |
| 06 Prototype | 13 click-through links: the main desktop journey, the English and Spanish switch, and the phone menu journey | Simulated navigation between images and wireframes |
| 07 Components tokens | Primary and secondary button (secondary: default, hover, focus, disabled at 200 px wide; hover changes only the background and text colour, as in the build), status badge (6 statuses), text input (4 states), link (3 states), header and footer (desktop and mobile) | Editable components |

## Design tokens
Colours, type and spacing are documented in `design/design-tokens.md`.
- **Primary** #0B6E99 for links and buttons (5.67:1 on white). The bright accent #1CABE2 is decorative only and never used for text on white.
- **Type:** Noto Sans (SIL Open Font License), five sizes from 14 px to 36 px, each with a fixed line height. No text below 14 px.
- **Spacing:** an 8 px grid, with 4 px and 12 px half-steps.
- **Focus ring:** 3 px solid #1A1A1A outside the element.
- **Status** is never shown by colour alone: each of the six statuses (Not started, In progress, On track, Delayed, Completed, No recent update) has a label and an icon.

## How the design maps to the build
- Every wireframe section matches a section of the built page: the commitment detail page lists notices, metadata, progress updates, milestones, and source and licence; the country profile lists commitments, indicators, trend charts and data sources.
- Each chart has a data table as its text alternative.
- The Spanish interface and a right-to-left layout test are built in the site; the Figma file shows Spanish at desktop width only.

## What is a design proposal and what is built
- The **admin review queue** wireframe proposes a review panel with a verification checklist and a decision control. The built site uses the standard Drupal content list and the record's edit page for the same decision.
- The **mobile menu** wireframe states its keyboard behaviour (closes on Esc, focus returns to the button) as design intent.
- The **hi-fi pages are images** captured from the running site on 2026-11-01. They show the finished result, not editable layers.

## Not yet in the file
- The tablet layouts as editable frames (tablet is covered by the reference screenshots on page 05).
- Pages 01 and 02 (information architecture, user journeys), which exist as documents in `docs/`.

## Accessibility evidence
Automated axe checks cover 63 page, language and size combinations with no violations; manual VoiceOver, zoom (200 and 400 percent) and greyscale checks are recorded in `docs/manual-accessibility-checks.md`. Lighthouse results are in `evidence/lighthouse/README.md`.

## Sources and licences
Fonts: Noto Sans, SIL OFL. Map outlines: Natural Earth, public domain. Chart library: ECharts, Apache-2.0. See `design/third-party-assets.md`.
