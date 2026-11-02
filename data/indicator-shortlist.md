# Indicator shortlist and licence check v0.1

Checked 2026-10-26. Purpose: choose real public indicators for the tracker and record whether each source's terms allow its values to be stored in this repository and shown on the site. Related: `docs/data-approach.md` (rule 5), `data/data-dictionary.md` (Indicator, Indicator value), RTM S5 and D2g.

**Method.** Terms were read on the publisher's own page where it could be retrieved. Where a primary page could not be retrieved, the source is marked *Unconfirmed* and no values are added until it is checked by hand in a browser. Licence status is recorded per source here and must be re-confirmed per indicator at retrieval, because dataset metadata can override a site-wide default.

## 1. Licence check by source
| Source | What was found | Status | Decision |
|---|---|---|---|
| WHO data portal (data.who.int) | Datasets are provided under CC BY 4.0 unless stated otherwise; attribution must follow the format in the dataset metadata; must not state or imply WHO endorsement or use its name or emblem without written approval; material credited to other sources needs that source's permission. [Terms](https://data.who.int/redirect-pages/footer/learn-more/terms-and-conditions) | Confirmed (primary page) | Usable with attribution. Check each indicator's metadata. |
| WHO publications and legacy GHO pages | Secondary sources report CC BY-NC-SA 3.0 IGO for WHO publications and for older GHO records. Not confirmed on a WHO page. | Unconfirmed | Prefer the data.who.int version of any indicator. Do not copy values from publication PDFs into the repository without checking the licence printed in that document. |
| World Bank Open Data | Default CC BY 4.0 for World Bank datasets; credit in the form "The World Bank: Dataset name: Data source"; must not imply World Bank endorsement; some third-party indicators may not be redistributed without the original provider's consent. [Summary terms](https://data.worldbank.org/summary-terms-of-use) | Confirmed (primary page) | Usable with attribution. Read each indicator's metadata for third-party restrictions. |
| UNICEF Data Warehouse / data.unicef.org | No site-wide data licence found. Individual reports state their own licences (several CC BY 4.0, some CC BY-NC-SA 3.0 IGO); photographs and logos are excluded; the general UNICEF website terms reserve copyright. The terms page itself returned an access error. | Unconfirmed | Do not store UNICEF values yet. Link to the source and revisit after checking in a browser or asking UNICEF. |
| IHME Global Burden of Disease (GBD) | Data is available under the IHME Free-of-Charge Non-Commercial User Agreement, which the user accepts personally and which restricts commercial use and sharing with commercial parties. The agreement text could not be retrieved in full. | Restricted / unconfirmed | Do not store GBD values in the public repository. Show as a link-out only, or exclude. |
| WHO Mental Health Atlas 2024 (released September 2025) | Report and country-profile data. No licence statement retrieved from WHO. | Unconfirmed | Look for the Atlas dataset on data.who.int (CC BY 4.0 default); use only that version. |

## 2. Shortlisted indicators (proposed)
Selection criteria: relevant to child and adolescent mental health, available for all eight starter countries, a defined unit and age group, a primary source with a usable licence, and a text description that makes sense to a non-specialist. Codes and exact names are confirmed when each series is retrieved.

| # | Indicator (working name) | Theme | Candidate source | Licence route | Status |
|---|---|---|---|---|---|
| I1 | Suicide mortality rate, per 100,000 population (SDG indicator 3.4.2) | Data and monitoring | World Bank Open Data (series compiled from WHO) or data.who.int | CC BY 4.0, check third-party note | Metadata checked for Kenya on 2026-10-26: see findings below. The project author confirmed on the World Bank page that all eight starter countries show a value |
| I2 | Suicide mortality rate, ages 15 to 19 (if available as a dedicated series) | Prevention and promotion | data.who.int | CC BY 4.0 | Candidate, availability to verify |
| I3 | Mental health workers per 100,000 population | Services and workforce | data.who.int (Mental Health Atlas data) | CC BY 4.0 if published there | Candidate, availability to verify |
| I4 | Government mental health expenditure, share of health budget | Financing | data.who.int (Mental Health Atlas data) | CC BY 4.0 if published there | Candidate, availability to verify |
| I5 | Existence of a national mental health policy or plan (yes/no) | Policy and legislation | data.who.int (Mental Health Atlas data) | CC BY 4.0 if published there | Candidate, availability to verify |
| I6 | Population aged 0 to 14 (World Bank series SP.POP.0014.TO, a count; the share-of-population series SP.POP.0014.TO.ZS is the better comparison measure) | Context | World Bank Open Data | CC BY 4.0, licence line read on the indicator page by the project author on 2026-10-26 | Licence and unit confirmed (see below); country values exist |

I2 to I5 depend on whether WHO publishes them as downloadable datasets with a clear licence. If an indicator is only in a PDF or an unconfirmed source, it is dropped or shown as a link-out. The tracker should work with I1, I6 and any of I2 to I5 that pass the check; a minimum of three indicators suffices for the dashboard demonstration.

### Findings from the manual check (2026-10-26)
- **World Health Statistics 2026 (WHO publication).** It lists suicide mortality (SDG 3.4.2) and gives figures by WHO region only, not by country. It is a publication, so its licence is separate from the data portal's and is unconfirmed. It is used as background reading only: no text or figures are copied into the repository. Country-level suicide rates must come from a downloadable dataset (WHO data portal or World Bank).
- **World Bank SP.POP.0014.TO.** Country-level values exist; licence CC BY 4.0. The Kenya 2025 value displayed was 20,860.01; since Kenya's child population is in the tens of millions, this is probably in thousands. Confirm the unit in the indicator's metadata before use.

### I1 metadata (World Bank series SH.STA.SUIC.P5, read for Kenya by the project author, 2026-10-26)
- **Unit:** per 100,000 population. Crude rate (not age-adjusted), so it covers all ages and is not specific to children or adolescents; it is context for the tracker, not a child-specific measure.
- **Source:** World Health Organization, Global Health Observatory Data Repository, derived from WHO Global Health Estimates; World Bank dataset WB_WDI.
- **Licence:** CC BY-4.0.
- **Coverage:** annual, reference period 2000 to 2021 (latest year is 2021).
- **Caveat:** the World Bank notes revisions are not comparable with previously published estimates; estimates may differ from official national figures. Show this note beside the data.
- **Attribution to display:** "The World Bank: Suicide mortality rate (per 100,000 population): World Health Organization, Global Health Observatory", with the licence name.

### I6 metadata (World Bank series SP.POP.0014.TO, read by the project author, 2026-10-26)
- **Unit of measure:** "Unit", meaning a count of people. The World Bank API returns Kenya 2025 as 20,860,012 (2024: 20,787,672; 2023: 20,722,869). The web page displayed 20,860.01, which is the same number shown in thousands. Stored values use persons.
- **Source:** World Bank staff estimates, using the World Bank's total population and the age/sex distributions of the UN Population Division's World Population Prospects.
- **Licence:** CC BY-4.0. The underlying UN Population Division data is a third-party input; the World Bank dataset page carries the CC BY-4.0 licence for this series.
- **Coverage:** annual, 1960 to 2025, mid-year estimates; includes projections for recent years.
- **Attribution to display:** "The World Bank: Population ages 0-14, total: World Bank staff estimates based on UN Population Division, World Population Prospects", with the licence name.

## 3. Attribution text (to store in `source_name` and show on screen)
- World Bank: "The World Bank: [dataset name]: [data source]." plus the licence name and link.
- WHO: the citation format given in the dataset's metadata, or "World Health Organization, [year], data.who.int" if none is given.
- Every indicator page states that this project is independent and not endorsed by, or affiliated with, WHO or the World Bank. WHO and World Bank names appear only as source credits; no logos.

## 3a. Retrieval log
2026-10-26: values for I1 (SH.STA.SUIC.P5, 2000 to 2021) and I6 (SP.POP.0014.TO, 2000 to 2025) retrieved for all eight starter countries from the World Bank API (data last updated by the World Bank on 2026-10-08) and stored in `data/indicator_values.csv`. I2 to I5 not retrieved (availability and licence still to be confirmed), so a third indicator is still open.

## 4. Next steps
1. In a browser, open data.who.int and the World Bank Data Catalog and confirm whether I1 to I6 exist for the eight starter countries; record exact names, codes, units, years and metadata licence lines here.
2. Check the UNICEF Data Warehouse terms in a browser (or contact data@unicef.org) and update this table.
3. Retrieve values only for confirmed indicators; store each with `retrieved_on` and the licence field populated.
4. Update `docs/data-approach.md` rule 5 to reflect these results.

## Limitations
Licence readings are summaries by the project author, not legal advice. Two of five sources could not be fully confirmed because their terms pages were not retrievable by the tool used. No indicator values have been added to the repository.

## Availability check (2026-10-27)
- World Bank: a search of all 29,544 indicator names found no series for mental health workers, psychiatrists or mental health expenditure. Only the three suicide mortality series (total, female, male) exist. I3 to I5 are therefore not available from the World Bank.
- WHO Global Health Observatory indicator list (ghoapi.azureedge.net/api/Indicator) does list Mental Health Atlas series: MH_21 total workforce in the mental health sector (per 100,000), MH_6 psychiatrists, MH_7 nurses, MH_4 government mental health expenditure as % of health expenditure, MH_1 mental health policy, MH_2 mental health plan, MH_3 stand-alone legislation. The data endpoint for MH_21 returned a server error (HTTP 500) when tried, so country coverage, years and licence are unconfirmed. data.who.int search did not surface these for the author.

2026-10-27 (author check, WHO Global Health Observatory map for the mental health workforce series): the map shows many countries as "Data not available", including at least the United Kingdom, and the series list under "mental health sector" is split by profession (psychologists, psychiatrists, nurses, social workers, occupational therapists, other doctors) with no single combined series visible. Coverage of the eight starter countries is therefore incomplete and unconfirmed. Decision: I3 to I5 are not charted as indicators in the prototype. Where useful, the country profile links out to the WHO Mental Health Atlas. The prototype's indicator set is I1 (suicide mortality) and I6 (population 0 to 14). A third indicator is optional and can be revisited later.
