# Information architecture, sitemap and navigation

Proposed structure for the tracker website. Related RTM rows: D1b, Q5, S3, W3, W4, W6. Related requirements: FR-01 to FR-25 (`docs/requirements.md`). Audience assumptions are in `docs/audiences.md`.

## Design principles
1. Summary first, detail on request: overview → country → commitment.
2. Every number shows its source and last-updated date.
3. Three public entry routes: by map/overview, by country, by search/filters.
4. Admin functions live behind login and are separate from the public navigation.
5. The same structure works in English, Spanish and right-to-left layout.

## Sitemap (public)
```
Home (overview)
├── Tracker
│   ├── Overview dashboard (global KPIs, map, progress summary, recent updates)
│   ├── Commitments (searchable, filterable table)
│   │   └── Commitment detail
│   │       (description, institution, milestones, status, timeline, progress,
│   │        sources, related documents, change history summary)
│   ├── Indicators (charts + table by indicator)
│   │   └── Indicator detail (definition, source, licence, trend)
│   └── Countries
│       └── Country profile (key indicators, commitments, documents)
├── Resources (document repository: filter by country, type, theme, year, language)
│   └── Document detail
├── Data (download CSV/JSON, data dictionary, how data is updated)
├── About
│   ├── About this project (including independent-project disclaimer)
│   ├── Methodology and data sources
│   └── Glossary (plain-language definitions)
├── Accessibility statement
├── Privacy and cookies
└── Contact
```

## Sitemap (authenticated)
```
Log in
├── Contributor workspace
│   ├── My country/ministry commitments
│   ├── Add / edit commitment → milestones → progress update → documents
│   └── Submissions and their status (draft, in review, published, rejected)
├── Editor / reviewer
│   ├── Review queue (with approve/reject + comment)
│   └── Data-quality report
└── Administrator
    ├── Import and refresh (CSV, simulated API feed, import log)
    ├── Users and roles
    └── Site configuration
```

## Navigation
**Global header (all pages):** logo/name · Tracker · Countries · Resources · Data · About · Language switcher (EN / ES) · Search · Log in.
**Footer:** Accessibility · Privacy and cookies · Contact · Data sources · Independent-project disclaimer.
**Mobile:** header collapses to a menu button; search and language switcher remain visible; filters open in a panel.
**Tracker sub-navigation (tabs on tracker pages):** Overview · Commitments · Indicators · Countries.
**Breadcrumbs** on all pages below the second level.

## URL pattern (friendly, multilingual)
`/en/tracker/commitments/kenya-school-mental-health-policy` ; `/es/seguimiento/compromisos/...` (language prefix; readable slugs; translated paths where practical).

## Content model implications (input to Phase 3)
Commitment · Indicator · Institution · Country · Milestone · Progress update · Document · Static page. Country, theme, status, document type and region are taxonomies (controlled vocabularies).

## Status vocabulary (proposal)
Not started · In progress · On track · Delayed · Completed · No recent update. Each status has a text label and an icon in addition to colour.

## Open items
- Final labels and wording to be tested in plain language (Phase 2 usability check).
- Whether "Indicators" and "Commitments" deserve separate top-level pages or one combined tracker (decide when wireframing).
