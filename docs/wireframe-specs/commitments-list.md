# Wireframe spec: Commitments list

Screen: Tracker → Commitments (`/en/tracker/commitments`). Frames: desktop 1280 and mobile 375, on Figma page "03 Wireframes low-fi".
Related requirements: FR-02, FR-03, FR-04, FR-07, FR-08, FR-21, FR-23, NFR-01, NFR-02. Related RTM rows: S4, D1a, D1b, D2b, D2c, D2d.

## Purpose
Let any visitor find commitments quickly by browsing, filtering or searching, and open a commitment's detail page. This is the main "by search/filters" entry route defined in `docs/information-architecture.md`.

## Audiences
Public (youth, civil society, researchers) and donors. Primary task: "Show me the commitments for a given country, theme or status." Secondary: export the filtered list (FR-09, linked to the Data page).

## Layout, top to bottom (desktop)
1. **Header** (existing `Header / Desktop` component).
2. **Breadcrumb:** Home › Tracker › Commitments.
3. **Tracker tabs:** Overview · **Commitments** (active) · Indicators · Countries.
4. **Page title and intro:** H1 "Commitments"; one sentence on what a commitment is; "Data last updated: [date]" and a link to Methodology and data sources (FR-07).
5. **Illustrative-data notice:** shown only when the result set includes illustrative records (FR-08). Text: "Some records are illustrative and marked as such."
6. **Filter bar** (one row, wraps on narrower widths):
   - Search (text input, label "Search commitments")
   - Country (select)
   - Region (select)
   - Theme (select)
   - Status (select; values from the status vocabulary)
   - Year (select)
   - "Clear all filters" link
7. **Results bar:** "Showing N of M commitments"; sort select (Most recently updated, Country A–Z, Status); view toggle **Table | Map** (FR-02: map view with table alternative).
8. **Active filter chips:** one removable chip per applied filter.
9. **Results table** (default view).
10. **Pagination:** previous / page numbers / next; page size 20.
11. **Footer** (existing `Footer / Desktop` component).

## Results table columns
| Column | Content | Notes |
|---|---|---|
| Commitment | Title, linked to detail page | Sortable |
| Country | Name | Text only in the wireframe; no flags |
| Institution | Responsible institution | |
| Theme | Taxonomy term | |
| Status | Status badge (existing component): icon + text label + colour | Never colour alone |
| Last updated | Date | Source link available on the detail page |
| Record type | "Illustrative" tag where applicable | FR-08 |

Row interaction: the title is the link; the whole row highlights on hover. Row actions are not shown on the public view.

## Placeholder content in the wireframe
Use obviously generic placeholder text ("Commitment title placeholder", "Country name", "Institution name") with a mix of all six statuses so every badge appears. Do not place invented commitments against real countries, per `docs/data-approach.md`.

## States
| State | Behaviour |
|---|---|
| Default | Table with 20 rows, filters empty |
| Filtered | Chips shown, count updated, focus returns to results heading |
| Loading | Skeleton rows; "Loading commitments" announced to assistive technology |
| No results | Message "No commitments match these filters", with "Clear all filters" button and a suggestion to broaden the search |
| Error | Message "Commitments could not be loaded", with a Retry button |
| Map view | Map with an adjacent list of the same results; table remains reachable by the toggle |

Each state is drawn as a separate frame variant only if time allows; the default and no-results states are required for this phase.

## Mobile (375)
- Header collapses to the mobile header component.
- Tracker tabs become a horizontally scrollable row (no page-level horizontal scroll).
- Search stays visible. All other filters move into a **Filters** button that opens a full-height panel with an "Apply" button and a result count.
- The table becomes a stacked card list: title, country, status badge, last updated. Institution and theme appear on the detail page.
- Pagination becomes "Previous / Next" with a page indicator.

## Accessibility notes (NFR-01)
- Every filter has a visible label; no placeholder-only labels.
- Filter changes update the result count in a polite live region.
- Table uses real header cells; sort controls are buttons with an accessible name and current sort state.
- Status is conveyed by icon and text as well as colour.
- Focus order follows the visual order; visible focus ring on all controls.
- Touch targets at least 44 px on mobile.

## Components used
Existing: Header, Footer, Status badge, Text input. Pending (to be added during hi-fi as needed): Select, Filter chip, Pagination, Secondary button, Link.

## Out of scope for the wireframe
Real data, final copy, the map rendering itself, saved filters, and Spanish and right-to-left variants (tested later against the hi-fi design).

## Open items
- Confirm whether Region and Year filters are needed on the first screen or sit under "More filters" (decide after the first usability check).
- Decide whether the Map view lives on this page or only on the Overview (IA open item).
