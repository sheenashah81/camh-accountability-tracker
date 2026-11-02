"""Export every AI-assisted Spanish string with its English source into one review sheet.

Run from the repository root:  python3 web/scripts/export_spanish_review.py
Output: data/review/spanish-review-sheet.csv
"""
import csv, re

OUT = "data/review/spanish-review-sheet.csv"
rows = []

# Interface strings from the module translation file
po = open("web/web/modules/custom/camh_tracker/translations/es.po", encoding="utf-8").read()
for m in re.finditer(r'msgid "((?:[^"\\]|\\.)*)"\nmsgstr "((?:[^"\\]|\\.)*)"', po):
    if m.group(1):
        rows.append(("Interface text", "", m.group(1).replace('\\"', '"'), m.group(2).replace('\\"', '"')))

def add(path, kind, key, pairs):
    for r in csv.DictReader(open(path, encoding="utf-8")):
        for en, es in pairs:
            if r.get(es):
                rows.append((kind, r[key] + " / " + en, r[en], r[es]))

add("data/commitments.csv", "Commitment", "slug", [("title_en", "title_es"), ("description_en", "description_es")])
add("data/milestones.csv", "Milestone", "commitment_slug", [("title_en", "title_es")])
add("data/progress_updates.csv", "Progress update", "commitment_slug", [("summary_en", "summary_es")])
add("data/vocabularies.csv", "Vocabulary term", "term_code", [("label_en", "label_es")])

with open(OUT, "w", newline="", encoding="utf-8") as f:
    w = csv.writer(f, lineterminator="\n")
    w.writerow(["id", "type", "where", "english", "spanish_current", "reviewer_correction", "reviewer_comment"])
    for i, (k, where, en, es) in enumerate(rows, 1):
        w.writerow([i, k, where, en, es, "", ""])
print(len(rows), "strings written to", OUT)
