# Roadmap and open items

The work queued next and anything awaiting confirmation, in one place. Updated 2026-10-30. Items marked **[you]** need a person to open a document, decide something or review text; the rest can be done in a working session.

## Data and sources
| Item | Detail | Needs |
|---|---|---|
| Statuses for draft commitments (In progress set 2026-10-29 for Kenya, Nigeria, South Africa, Jordan, Philippines and both Bangladesh records; Colombia set to In progress on 2026-10-31 from two Ministry of Health documents issued under Ley 2460; the children's protocol that the law requires within one year of June 2025 is still to be located) | A commitment needs a status backed by public evidence before it can be published. Only the UK record has one (On track, from the 10 June 2026 GOV.UK release) | [you] public progress evidence per country |
| Non-commercial source terms | Kenya and South Africa allow non-commercial use only. Any page showing those records must repeat the terms; this prototype is non-commercial | decision before any commercial use |
| Nigeria | Closed 2026-10-28: licence recorded as Other (link and short paraphrase only); the Ministry download is unavailable, so the Gazette copy is cited. Date is the one printed in the Act. Remaining: progress status, shared with the other countries | none |
| Jordan | Closed: source is the MHIN copy of the 2011 policy (blocks automated checks); licence Other, link and paraphrase only. The 2022-2026 action plan was not located and is not used | none |
| South Africa | Evidence added 2026-10-30 (Western Cape adolescent unit, 18 June 2026). Page 33 = infrastructure table, page 18 = section 2.5. Remaining: match the district-system and specialised-hospital passages to pages 24, 25, 29 or 30 | [you] optional |
| Bangladesh | Strategic Plan closed 2026-10-28 (published 2022-05-21 per DGHS notice). Plan of Action: no publication date in the document, left blank by design and required only before review; licence recorded as Other | none |
| Colombia | The Función Pública link is confirmed and is the cited source. The Foreign Ministry and judicial-branch copies are optional extras and are not needed | none |
| Illustrative records | None exist. The rule is that records added only to demonstrate workflows are flagged illustrative and never name a real government as author | decide whether to add any |
| Third indicator | Dropped: WHO workforce data has gaps for the starter countries | optional later |

## Review of text
| Item | Detail |
|---|---|
| Spanish | All Spanish text is AI-assisted. The 189 strings in `data/review/spanish-review-sheet.csv` (interface text, commitment titles and descriptions, the milestone and progress update, vocabulary terms) were reviewed by a person on 2026-11-01 with no corrections, and the pending label was removed from them. New or changed Spanish text keeps the label until it is checked |
| English wording | Plain-language check of theme and status names (data dictionary open item) and a read-through of every public description against its source; Excel sheet with a Yes / No / Partly drop-down at `data/review/english-read-through-sheet.xlsx` ([you]) |

## Product
| Item | Detail |
|---|---|
| Map | Not zoomable; the list map shows only countries in the current results |
| Admin import | Commitments can be imported through the admin form (done). Indicator values can also be imported through their own admin form (done, loaded unpublished for review). Other content types are still loaded by script, and the form keeps no stored import history beyond its on-screen report |
| Contributor and Editor accounts | Roles and the country restriction (rule V11) are covered by Behat journeys with test accounts (done) |
| Paging | Tested with 23 published records (Behat); a tie-break sort was added after the test found repeated and missing records (B-012) |
| Data-quality report | Built (FR-19) for commitment records and for indicator values (years held, gaps, unchecked licences, missing sources, data more than four years old) |
| Revision log display | Revisions are recorded by Drupal; no friendly "what changed" screen |
| Security hardening | Local admin password is development only; the production hosting, backup, HTTPS and update plan is written (`docs/hosting.md`) and awaits a hosting decision |

## Testing and accessibility
| Item | Detail |
|---|---|
| Manual accessibility | Sheet and results at `docs/manual-accessibility-checks.md`: zoom to 200 and 400 percent and greyscale pass; VoiceOver first pass found no problems (a detailed rotor and form-control pass is still worth doing); reduced motion not tested by hand. The right-to-left layout test is done (automated, English text mirrored at /ar); a native Arabic reader should review it if Arabic is ever added |
| Behat tests | Done (34 scenarios, including the contributor journey: draft for the assigned country saved, another country refused, no Published option) and the editor journey (approve and publish a record in review, reject a record in review). No Behat journeys are missing |
| Pa11y or Lighthouse | Lighthouse run on 2026-11-01 (accessibility and best practices 100, SEO 100, performance 91 to 100; results in evidence/lighthouse). Pa11y is optional and not run |
| CI | A red run on GitHub (a deliberate failure) has not been observed; runs on main and pull requests only; GitHub warns about Node.js 20 actions |

## Design (Figma)
The Figma file has the design system and the Home / Overview wireframes. The Commitments list wireframe (desktop, no results, mobile) was built on 2026-10-28 and checked against a screenshot on 2026-10-31; two clipped items (table header, long status badge) were fixed. Still to do: commitment detail, country profile, contributor form, admin review queue, mobile menu, then high-fidelity screens, a prototype, and the handover specification. The built site is the more complete artefact; the Figma side lags behind it.

## Comparison and documentation items from the plan
- Power BI comparison and integration design: written (`docs/integrations-and-power-bi.md`).
- DHIS2 public demo read and DevInfo integration: documentation or simulation only.
- Items that cannot be claimed (for example experience with Drupal 8 or 9 sites): see `docs/rtm.md`.
- Before making the repository public: review the commit history and every shareable file once more.
