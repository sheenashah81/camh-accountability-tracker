# Data approach and country selection

Decided 2026-10-21 (supersedes the earlier "fully synthetic" assumption in the plan).

## Rules
1. **Indicators:** real public data. Every record stores source name, URL, licence, and retrieved-on date.
2. **Commitments:** only where a public document supports them (policy, law, pledge). Store source URL and verified-on date.
3. **Illustrative records:** any record added only to demonstrate workflows (e.g. admin updates, milestone progress) is flagged `is_illustrative = true` and labelled on screen.
4. **Never** present an invented commitment as a government's actual position.
5. **Licences:** read and record each dataset's terms before adding data to the repository. Checked 2026-10-26, see `data/indicator-shortlist.md`: WHO data portal and World Bank confirmed (CC BY 4.0 with attribution); UNICEF Data, IHME GBD and WHO Mental Health Atlas licence unconfirmed or restricted, so no values are stored from them yet.

## Starter countries (author's selection, not an official UNICEF/WHO list)
| Region | Country | Reason |
|---|---|---|
| East Africa | Kenya | Author's context; consultancy duty station |
| West Africa | Nigeria | Large child population; author's research link |
| Southern Africa | South Africa | Strong policy documents and data |
| South Asia | Bangladesh | Adolescent mental health programming; regional variety |
| Southeast Asia | Philippines | National mental health law |
| Latin America | Colombia | Spanish-language layer |
| MENA | Jordan | Arabic / right-to-left relevance |
| Europe | United Kingdom | High-income comparator |

## Languages
English (reviewed by the author) and Spanish (AI-assisted and given a first review by a person on 2026-11-01, not yet by a native or professional Spanish speaker, which is required before real-world use; any new Spanish text is labelled "pending human review" until a person checks it). A right-to-left placeholder layout is used to test layout direction handling; it does not by itself prove the whole architecture.

## Open items
- Confirm remaining licences (see rule 5 and `data/indicator-shortlist.md`).
- Identify which public commitment documents exist per country.
