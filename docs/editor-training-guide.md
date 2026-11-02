# Editor training guide

A hands-on session for new contributors and editors of the tracker. It takes about 90 minutes and uses a practice (illustrative) record on a test copy of the site, never live data. For reference after the session, see `docs/admin-guide.md`.

This is an independent portfolio prototype, not an official UNICEF or WHO system.

## Learning outcomes
By the end of the session a participant can:
1. Explain what the tracker publishes and what it never publishes.
2. Add a commitment with complete provenance and submit it for review.
3. Review a submission and approve or reject it correctly.
4. Judge whether a public source supports a progress status.
5. Check a licence before reusing any wording or data.
6. Import a batch of records and read the error report.
7. Use the data-quality report to find what is outstanding.
8. Add a Spanish translation and keep the review label honest.

## Session plan
| Time | Topic | Activity |
|---|---|---|
| 0:00 | 1. What the tracker is | Tour the public pages |
| 0:10 | 2. Roles and the review workflow | Walk through the four states |
| 0:20 | 3. Adding a commitment | Exercise A |
| 0:40 | 4. Reviewing and publishing | Exercise B |
| 0:55 | 5. Evidence and statuses | Exercise C |
| 1:10 | 6. Licences | Short quiz |
| 1:15 | 7. Importing and the quality report | Exercise D |
| 1:30 | Close | Questions |

## 1. What the tracker is
It holds public commitments on child and adolescent mental health and cited public indicators for eight starter countries, in English and Spanish. Every record shows its source, licence and the date it was verified. Visitors see **published** records only; everything else is invisible to them, in the pages, the downloads and the API.

**Tour:** open Overview, Commitments, one commitment, one country profile, Indicators and Data. Find the source and licence panel on a commitment, and the table beneath a chart.

## 2. Roles and the workflow
| Role | Can do |
|---|---|
| Contributor | Create and edit their own records for their assigned country; save as Draft or submit for review |
| Editor | Edit all records, approve and publish, reject, translate |
| Administrator | Everything, including people and configuration |

| State | Meaning | Who can move it on |
|---|---|---|
| Draft | Being written; private | Contributor or Editor |
| In review | Waiting for an editor | Editor: Publish or Reject |
| Published | Public | Editor |
| Rejected | Returned with a comment | Contributor revises |

Two safeguards to remember: a record cannot be published while its licence is "Not yet checked", and a Spanish version never becomes public before its English original.

## 3. Adding a commitment (Exercise A)
Use the practice country given by the trainer.
1. **Content, Add content, Commitment.**
2. Enter a title and a description of 50 to 1,200 characters in your own words.
3. Choose the country, then an institution that belongs to the same country.
4. Choose a theme and a commitment type.
5. Provenance: source name, source link, licence, and the date you checked the source. Tick **Illustrative record** for practice records.
6. Leave the status blank unless you have a dated public source for it.
7. Under **Change to** choose **Save as draft**, or **Submit for review** when it is complete.

**Check yourself:** try to submit without a date made. Read the message. Why is it there?

## 4. Reviewing and publishing (Exercise B)
1. Open **Content** and filter by state **In review**.
2. Open the record in one window and its source in another.
3. Ask: does the source say what the record says? Is the description in the contributor's own words? Are the licence and verified-on date recorded?
4. Under **Change to** choose **Published** if satisfied, or **Rejected** with a comment saying exactly what to fix.
5. Visit the public page and confirm it appears (or does not, if rejected).

## 5. Evidence and statuses (Exercise C)
A status is a claim about a government's progress, so it needs a source. Rules of thumb:
- Use dated official or agency documents that relate to **the specific action** in the commitment.
- Set aside AI-written summaries, undated pages, and system-level news that does not concern the action.
- Choose **In progress** when evidence is partial; leave the status blank when there is none.
- Record sources you set aside, and why, in the verification note.

**Exercise:** the trainer supplies three short extracts for one practice commitment. Decide which supports a status, which to set aside, and what the status should be. Compare with the trainer's answer. The worked example and search words are in `docs/status-evidence-guide.md`.

## 6. Licences
| Licence on the source | What you may do |
|---|---|
| Open Government Licence v3, CC BY 4.0 | Reuse with the credit shown on the record |
| CC BY-NC-SA 3.0 IGO | Reuse non-commercially, with credit and the same licence |
| Public domain | Reuse freely |
| Other, see note | Link and paraphrase briefly only; no copying of text |
| Not yet checked | Cannot be published |

**Quiz:** (1) A ministry page has no copyright notice. Which licence do you record, and what do you do with its text? (2) A news article summarises a law. Do you cite the article or the law? *(Answers: 1. Other, see note; link and paraphrase. 2. The law, wherever it is published; the article is context.)*

## 7. Importing and the quality report (Exercise D)
1. Open **Content, Import commitments (CSV)** and upload the practice file.
2. Choose **Check only**. Read the report: Valid, Skipped or Error, with the reason for each.
3. Correct the file and check again until errors are understood.
4. Choose **Check and import**. Valid rows arrive as Drafts and still need review.
5. Open **Reports, Data-quality report**. Find the new records and read their outstanding items.

## Common messages
| You see | It means |
|---|---|
| Before review, add: date made, status | Required fields are missing for submission |
| Licence "Not yet checked" cannot be published | Record the licence first |
| Institution does not belong to the country | Choose an institution from the same country |
| Row has N values but the header has M | A comma is missing or extra in the CSV row |
| A commitment with this title already exists | The import skipped a duplicate |
| You may only create records for your assigned country | Contributor limit; ask an administrator |

## Spanish (short demonstration)
Open a record, choose **Translate**, add the Spanish title and text, and keep **Pending human review** until a Spanish reader has checked it. Mark it reviewed only after a person who reads Spanish has checked it.

## Trainer notes
- Prepare a test copy of the site with a practice contributor (one country) and a practice editor.
- Prepare Exercise C extracts and Exercise D files in advance; `tests/behat/fixtures/import-commitments.csv` is a ready example with one valid and one invalid row.
- Do not demonstrate on live records.
