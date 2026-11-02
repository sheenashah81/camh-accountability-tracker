# Audiences and assumptions

> **All content below is an assumption. None of it has been validated with real users or stakeholders.** It is the starting point for requirements, to be tested later with a small informal usability check (Phase 9). The TOR says only "multiple audiences" and names none.

Related RTM rows: D2d, Q4, Q13, D1b.

## 1. Public and advocates (assumed, unvalidated)
Journalists, civil society organisations, researchers, young people and caregivers.

| | |
|---|---|
| **Goals** | See what a government has promised on child and adolescent mental health; check whether it is on track; compare countries; download or cite evidence. |
| **Likely frustrations** | Jargon; unclear status labels; charts that cannot be read without colour or a mouse; slow pages on mobile or weak connections; not knowing where numbers come from. |
| **Needs** | Plain-language labels; a visible source and "last updated" date on every number; mobile-first layout; keyboard and screen-reader support; text alternatives for charts and maps; a downloadable table. |
| **Key tasks** | Find a country → see its commitments → open one commitment → read status, milestones and sources → download the data. |
| **Access** | No login. |

## 2. Government and agency focal points / data contributors (assumed, unvalidated)
Ministry or agency staff, or partner organisation staff, who submit or update commitments and progress for their country.

| | |
|---|---|
| **Goals** | Register a commitment; update milestone progress; attach a supporting document; see that the update was accepted and when it will appear publicly. |
| **Likely frustrations** | Long forms with unclear fields; losing work; not knowing validation errors; unclear approval stage; fear of publishing a mistake. |
| **Needs** | Short forms with inline help; clear validation messages; draft saving; a visible status (draft, in review, published); a history of changes; permission limited to their own country. |
| **Key tasks** | Log in → create or edit a commitment → add milestone update → upload document → submit for review → see outcome. |
| **Access** | Login; role "Contributor", limited to assigned country. |

## 3. Platform administrators (assumed, unvalidated)
Small central team who manage users, review submissions, check data quality and keep the platform running.

| | |
|---|---|
| **Goals** | Approve or reject submissions; fix data errors; manage users and roles; run imports and refreshes; monitor data freshness and errors. |
| **Likely frustrations** | No overview of what needs review; silent import failures; no audit trail; difficulty undoing a mistake. |
| **Needs** | Review queue; import log with errors; data-quality report (missing sources, stale updates); role management; change history; backup and restore notes. |
| **Key tasks** | Review queue → approve/reject with comment → run import → read validation report → manage a user. |
| **Access** | Login; roles "Editor" (reviews and publishes) and "Administrator" (users and configuration). |

## 4. International donors (added 2026-10-21; assumption from an external AI suggestion, unvalidated)
| | |
|---|---|
| **Goals** | See aggregated progress across countries or regions; export data to compare commitments and funding. |
| **Needs** | Global/regional summary views; CSV and JSON download of the public data; clear definitions and citations. |
| **Key tasks** | Open overview → filter by region/theme → download aggregated data. |
| **Access** | No login for public data. |

## 5. Youth advocates (sub-persona of Public, added 2026-10-21; unvalidated)
| | |
|---|---|
| **Goals** | Understand quickly what their government promised; share it. |
| **Needs** | Plain-language summaries without bureaucratic or medical jargon; simple status labels; shareable links (shareable image cards are a "could-have", not core scope). |

## Added constraints (from an external AI suggestion; sensible but unvalidated)
- **Low bandwidth:** public pages must stay lightweight and work on slow mobile networks. Use page caching; keep charts light and test on throttled mobile in Phase 9. (SVG is good for accessibility but can be heavy with many points, so this is a test goal, not a fixed rule.)
- **Contributors are likely a mix** of national ministry/agency staff and UN country office staff.
- **Access control requirement:** a contributor sees only their own ministry's drafts; a UN country-office reviewer can see and review all ministries within their own country. To be implemented with Drupal access-control tools chosen in Phase 4.

## Cross-cutting assumptions to test later
- Most public users arrive on mobile (assumed; Kenya and similar contexts often have mobile-first access and variable bandwidth).
- English and Spanish public content; the right-to-left layout is a technical test, not a claim about Arabic readers.
- Public users want a simple summary first and detail on request ("progressive disclosure").

## Open questions
- Real audience sizes and priorities (unknown).
- Whether contributors are government staff, UN country office staff, or both (unknown).
- Whether public users need accounts, alerts or sharing features (not in the TOR; out of scope unless added).
