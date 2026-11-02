# Administration guide

For editors and administrators of the tracker. Address of the local site: https://camh-tracker.ddev.site (it only works while Docker and the project are running; see `docs/maintenance-log.md`).

## Roles
| Role | Can do |
|---|---|
| Contributor | Create and edit their own commitments, milestones and progress updates, and submit them for review. Restricted to the country set on their user account ("Assigned country") |
| Editor | Edit all content, reject with a comment, approve and publish, translate |
| Administrator | Everything, including people, roles and configuration |

## Log in
Go to `/en/user/login`. Locally, an administrator can also run `ddev drush uli` in the `web/` folder to get a one-time login link (it works once).

## Add or change a commitment
1. Click **Content**, then **Add content**, then **Commitment**.
2. Fill in country, institution (it must belong to the same country), theme, type and a plain-language description of 50 to 1,200 characters.
3. Fill in the provenance: source name, source URL, licence and the date you verified the source. Records that are not illustrative need all of them.
4. Leave the status blank while a draft has not been assessed.
5. Under **Change to**, choose **Save as draft** or **Submit for review**.

## Review and publish (Editors)
1. Click **Content** and filter by moderation state **In review**.
2. Open the record, compare it with its source, then choose **Approve and publish**, or **Reject with comment** (a comment is required) which returns it to the contributor.
3. A record cannot be published while its licence is "Not yet checked", and it needs a date made, a status, a source and a verified-on date.
4. Publishing a progress update sets the commitment's status.

## Spanish
Open the record, click **Translate**, add the Spanish title and text, and leave "Spanish text review status" as **Pending human review** until a Spanish reader has checked it. A translation starts in the same state as the original, so a Spanish version of a draft is never public.

## Indicators
Indicator values are loaded from the CSV files by script (`web/scripts/build_content_model_3.php`). To add a dataset: add rows to `data/indicators.csv` and `data/indicator_values.csv` with source, licence and retrieval date, run `python3 tests/data/check_data.py`, then run the script.

## Importing commitments from a CSV file
Editors and administrators can load several commitments at once. Open **Content, Import commitments (CSV)** (`/admin/content/import-commitments`), choose a CSV that uses the column headings of `data/commitments.csv`, and select **Check only**. The report lists every row as Valid, Skipped or Error, with the reason for each error, and nothing is changed. Fix the file and check again, then select **Check and import**. Valid rows are created as Drafts and go through the normal review; the `workflow_state` column never publishes a record. Rows with the same title and country as an existing commitment are skipped. Keep the file at 2 MB or less.

### Importing indicator values

Open **Content, Import indicator values (CSV)** (`/admin/content/import-indicator-values`) and choose a CSV that uses the column headings of `data/indicator_values.csv`. **Check only** reports every row as Valid, Skipped or Error without changing anything. **Check and import** creates the valid rows unpublished, so nothing reaches the public Indicators or Data pages until an editor has reviewed it and published it from the content overview (select the values, then use the Publish action). The same rules apply as for manual entry: the indicator and country must exist, the year must be between 1990 and the current year, values cannot be negative (or above 100 for percentages), a real value needs a source link and a checked licence, and a value for the same indicator, country, year and sex is skipped.

## Data-quality report
Open **Reports, Data-quality report** (`/admin/reports/data-quality`). Each commitment is listed with its country, state and any outstanding items: no progress status, no date made, licence not checked, no source link, no verified-on date, or Spanish text pending human review. Drafts are expected to have open items; a record should have none before it is published. A second table covers indicator values by indicator and country: years held, any gaps, values with an unchecked licence or no source link or retrieved-on date, and data whose latest year is more than four years old (a prompt to check the source for newer data).

## Downloads
The Data page (`/en/tracker/data`) offers CSV and JSON of published records only.

## What to check before publishing anything
Source opens and says what the record says; licence recorded; date verified; description in your own words (no long quotations); status backed by a public source.
