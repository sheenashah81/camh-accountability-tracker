# User journeys

Three key journeys, one per core audience (`docs/audiences.md`). These are the flows prototyped in Figma. Related RTM rows: D1a, D1b, Q5. Requirements referenced are in `docs/requirements.md`. All persona details are assumptions, not validated research.

Format: **Step** (user action) → **System response** → **Requirements** → **Edge cases / states**.

## Journey 1: Public user finds a country's commitment (Public and advocates)
**Persona:** a journalist checking what Kenya has promised on school mental health. **Entry:** search engine or home page, on mobile. **Goal:** find the commitment, its status and its source in under two minutes.

| # | Step | System response | Requirements | Edge cases and states |
|---|---|---|---|---|
| 1 | Opens the home page | Overview: key indicators, map, progress summary, language switcher | FR-01, FR-21, NFR-02 | Slow connection: loading state; data unavailable: error state with retry |
| 2 | Selects Kenya on the map, or chooses it from a country list | Country profile: key indicators, commitment list, documents | FR-02, FR-03 | Map not usable (keyboard, screen reader): equivalent country list |
| 3 | Filters commitments by theme "School-based support" and status "In progress" | Table updates; filter chips show active filters; result count announced | FR-03, NFR-01 | No results: empty state explaining how to widen filters |
| 4 | Opens a commitment | Detail page: description, responsible institution, milestones, status, timeline, progress, sources | FR-05, FR-07, FR-08 | Illustrative record: visible label; stale data: "No recent update" status |
| 5 | Reads a chart of milestone progress | Chart with text/table alternative and source note | FR-06, NFR-01 | Chart fails to load: table shown instead |
| 6 | Downloads the data or opens the source document | CSV/JSON download or document detail with PDF | FR-09, FR-10 | File missing: clear error and contact link |
| 7 | Switches to Spanish | Same page at the `/es/` URL, content in Spanish | FR-21 | Untranslated item: shown in English with a notice |

**Success:** user finds the commitment, understands its status and can cite the source.

## Journey 2: Contributor submits a progress update (Contributors)
**Persona:** a ministry focal point updating a milestone for their own ministry's commitment. **Entry:** log-in page on a laptop. **Goal:** submit an accurate update for review.

| # | Step | System response | Requirements | Edge cases and states |
|---|---|---|---|---|
| 1 | Logs in | Contributor workspace listing only their ministry's commitments | FR-12, FR-15, NFR-06 | Wrong password: neutral error message; locked account: guidance to contact admin |
| 2 | Opens a commitment and chooses "Add progress update" | Form: milestone, new status, progress %, date, notes, source URL, optional document | FR-12 | Unsaved changes: draft autosave or leave warning |
| 3 | Fills in the form and attaches a PDF | Inline help; file type and size checks | FR-12, FR-10 | Unsupported or oversized file: specific error |
| 4 | Submits | Validation runs: required fields, date logic, progress between 0 and 100, source present | FR-13, NFR-08 | Errors summarised at top and marked at each field; focus moves to the summary |
| 5 | Sees confirmation | Status becomes "In review"; the change is not yet public | FR-14 | Submission fails: data preserved, retry offered |
| 6 | Later returns to check outcome | Status shown (published or rejected with reviewer comment) | FR-14, FR-16 | Rejected: edit and resubmit |

**Success:** update is saved, validated and visible to reviewers; the contributor knows what happens next.

## Journey 3: Administrator reviews, publishes and checks data quality (Administrators)
**Persona:** a UN country-office or platform reviewer. **Entry:** log-in on a laptop. **Goal:** approve a correct update, reject an incorrect one, and keep data trustworthy.

| # | Step | System response | Requirements | Edge cases and states |
|---|---|---|---|---|
| 1 | Logs in and opens the review queue | List of submissions awaiting review, oldest first, with country and ministry | FR-14, FR-15 | Empty queue: confirmation message |
| 2 | Opens a submission | Side-by-side: proposed values vs current published values; source and attachment | FR-14, FR-16 | Missing source: flagged |
| 3 | Approves or rejects, adding a comment | Approve publishes the update and refreshes dashboard figures; reject returns it with the comment | FR-14 | Rejection without comment: comment required |
| 4 | Opens the data-quality report | Lists stale updates, missing sources, failed validations | FR-19 | No issues: positive empty state |
| 5 | Runs a CSV import | Preview with row-level errors; confirm to apply; import log saved | FR-17, FR-18 | Bad file: no data changed, errors listed by row |
| 6 | Checks change history | Who changed what and when | FR-16 | |

**Success:** the public dashboard reflects only approved, validated, sourced data, and every change is traceable.

## Cross-journey state patterns (to design once, reuse everywhere)
Loading · Empty · Error with recovery action · Validation error summary · Success confirmation · Offline/slow network message · Untranslated-content notice · Illustrative-record label.

## Out of scope for the prototype
Public accounts, alerts, sharing to social media with generated image cards (a "Could" requirement, FR-24), single sign-on.
