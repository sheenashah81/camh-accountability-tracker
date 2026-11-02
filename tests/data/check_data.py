"""Consistency checks for the CSV files in data/. Standard library only.

Run from the repository root:  python3 tests/data/check_data.py
Exit status is 1 if any check fails.
"""
import csv
import sys
from datetime import date
from pathlib import Path

DATA = Path(__file__).resolve().parents[2] / "data"


def load(name):
    with open(DATA / name, newline="", encoding="utf-8") as f:
        return list(csv.DictReader(f))


errors = []


def check(ok, msg):
    if not ok:
        errors.append(msg)


countries = load("countries.csv")
institutions = load("institutions.csv")
vocab = load("vocabularies.csv")
commitments = load("commitments.csv")
indicators = load("indicators.csv")
values = load("indicator_values.csv")

terms = {}
for v in vocab:
    terms.setdefault(v["vocabulary"], set()).add(v["label_en"])

iso = [c["iso3"] for c in countries]
check(len(iso) == len(set(iso)), "countries: duplicate iso3")
for c in countries:
    check(len(c["iso3"]) == 3 and c["iso3"].isupper(), f"countries: bad iso3 {c['iso3']}")
    check(c["region"] in terms["region"], f"countries: unknown region {c['region']}")

codes = [i["code"] for i in institutions]
check(len(codes) == len(set(codes)), "institutions: duplicate code")
inst_country = {}
for i in institutions:
    inst_country[i["code"]] = i["country_iso3"]
    check(i["country_iso3"] in iso, f"institutions: unknown country {i['country_iso3']}")
    check(i["institution_type"] in terms["institution_type"], f"institutions: unknown type {i['institution_type']}")

slugs = set()
for r in commitments:
    k = r["slug"]
    check(k not in slugs, f"commitments: duplicate slug {k}")
    slugs.add(k)
    check(r["country_iso3"] in iso, f"{k}: unknown country")
    check(r["institution_code"] in inst_country, f"{k}: unknown institution")
    check(inst_country.get(r["institution_code"]) == r["country_iso3"], f"{k}: V6 institution country differs from commitment country")
    for t in r["theme"].split("|"):
        check(t in terms["theme"], f"{k}: unknown theme {t}")
    check(r["commitment_type"] in terms["commitment_type"], f"{k}: unknown commitment type")
    check(r["licence"] in terms["licence"], f"{k}: unknown licence")
    check(r["workflow_state"] in terms["workflow_state"], f"{k}: unknown workflow state")
    if r["status"]:
        check(r["status"] in terms["status"], f"{k}: unknown status")
    check(50 <= len(r["description_en"]) <= 1200, f"{k}: description length outside 50 to 1200")
    check(r["is_illustrative"] in ("true", "false"), f"{k}: is_illustrative must be true or false")
    if r["is_illustrative"] == "false":
        check(r["source_name"] and r["source_url"] if r["workflow_state"] != "Draft" else bool(r["source_name"]), f"{k}: P1 source missing")
    if r["date_made"] and r["target_date"]:
        check(r["target_date"] >= r["date_made"], f"{k}: V3 target_date before date_made")
    if r["workflow_state"] == "Published":
        check(r["licence"] != "Not yet checked", f"{k}: P1 cannot publish with unchecked licence")
        check(bool(r["verified_on"]), f"{k}: published without verified_on")
    if r["verified_on"]:
        check(r["verified_on"] <= date.today().isoformat(), f"{k}: verified_on in the future")

ind_codes = {i["code"] for i in indicators}
check(len(ind_codes) == len(indicators), "indicators: duplicate code")
for i in indicators:
    check(i["theme"] in terms["theme"], f"{i['code']}: unknown theme")
    check(i["direction"] in terms["indicator_direction"], f"{i['code']}: unknown direction")

seen = set()
for v in values:
    key = (v["indicator_code"], v["country_iso3"], v["year"], v["sex"])
    check(key not in seen, f"values: V5 duplicate {key}")
    seen.add(key)
    check(v["indicator_code"] in ind_codes, f"values: unknown indicator {v['indicator_code']}")
    check(v["country_iso3"] in iso, f"values: unknown country {v['country_iso3']}")
    check(1990 <= int(v["year"]) <= date.today().year, f"values: year out of range {key}")
    x = float(v["value"])
    if v["indicator_code"] == "SH.STA.SUIC.P5":
        check(0 <= x <= 100, f"values: V4 implausible suicide rate {key} = {x}")
    if v["indicator_code"] == "SP.POP.0014.TO":
        check(x > 0, f"values: V4 non-positive population {key}")
    check(v["licence"] in terms["licence"] and v["licence"] != "Not yet checked", f"values: licence not confirmed {key}")
    check(bool(v["source_name"] and v["source_url"] and v["retrieved_on"]), f"values: P1 provenance missing {key}")
    check(v["retrieved_on"] <= date.today().isoformat(), f"values: retrieved_on in the future {key}")
for code in ind_codes:
    got = {v["country_iso3"] for v in values if v["indicator_code"] == code}
    check(got == set(iso), f"values: {code} missing countries {sorted(set(iso) - got)}")

milestones = load("milestones.csv")
updates = load("progress_updates.csv")
slug_status = {r["slug"]: r for r in commitments}
for m in milestones:
    k = m["commitment_slug"]
    check(k in slug_status, f"milestones: unknown commitment {k}")
    check(m["milestone_status"] in terms["status"], f"milestones: unknown status {m['milestone_status']}")
    if m["completed_on"]:
        check(m["completed_on"] <= date.today().isoformat(), f"milestones: completed_on in the future ({k})")
        check(m["milestone_status"] == "Completed", f"milestones: completed_on set but status not Completed ({k})")
    check(m["is_illustrative"] in ("true", "false"), f"milestones: bad is_illustrative ({k})")
for u in updates:
    k = u["commitment_slug"]
    check(k in slug_status, f"progress updates: unknown commitment {k}")
    check(20 <= len(u["summary_en"]) <= 800, f"progress updates: summary length outside 20 to 800 ({k})")
    check(u["update_date"] <= date.today().isoformat(), f"progress updates: update_date in the future ({k})")
    check(u["new_status"] in terms["status"], f"progress updates: unknown status {u['new_status']}")
    if u["is_illustrative"] == "false":
        check(bool(u["source_url"]), f"progress updates: real update without source_url ({k})")

print(f"countries={len(countries)} institutions={len(institutions)} vocabulary terms={len(vocab)} commitments={len(commitments)} indicators={len(indicators)} indicator values={len(values)} milestones={len(milestones)} progress updates={len(updates)}")
if errors:
    print("FAILED:")
    for e in errors:
        print(" -", e)
    sys.exit(1)
print("All checks passed.")
