"""Automated accessibility and mobile check.

Opens each public page in headless Chrome at desktop (1280 px) and phone (375 px)
width, runs axe-core (WCAG 2.0, 2.1 and 2.2 level A and AA rules) and checks for
horizontal scrolling. Writes a Markdown report and JSON to evidence/accessibility/.

Usage (from the repository root):
    python3 -m venv .venv && .venv/bin/pip install -r tests/accessibility/requirements.txt
    .venv/bin/python tests/accessibility/axe_check.py [--base https://camh-tracker.ddev.site]

Needs Google Chrome. axe-core is downloaded on first use to tests/accessibility/.cache
(MPL-2.0, not committed). Automated rules find roughly a third of accessibility
problems: a keyboard and screen reader review is still required.
"""
import argparse
import json
import os
import shutil
import subprocess
import sys
import tempfile
import time
import urllib.request
from datetime import date
from pathlib import Path

import websocket

ROOT = Path(__file__).resolve().parents[2]
CACHE = Path(__file__).resolve().parent / ".cache"
AXE_URL = "https://cdn.jsdelivr.net/npm/axe-core@4.10.2/axe.min.js"
CHROME = os.environ.get("CHROME") or next(
    (c for c in ("/Applications/Google Chrome.app/Contents/MacOS/Google Chrome", shutil.which("google-chrome") or "",
                 shutil.which("chromium") or "", shutil.which("chromium-browser") or "") if c and os.path.exists(c)),
    "google-chrome")
PAGES = [
    ("Overview", "/{l}"),
    ("Commitments list", "/{l}/tracker/commitments"),
    ("Commitment detail", "/{l}/commitments/england-mental-health-support-teams-national-coverage-2030"),
    ("Country profile", "/{l}/countries/kenya"),
    ("Countries list", "/{l}/tracker/countries"),
    ("Indicators", "/{l}/tracker/indicators"),
    ("Data downloads", "/{l}/tracker/data"),
]
VIEWPORTS = [("desktop", 1280, 900, False), ("narrow-desktop", 1000, 700, False), ("phone", 375, 800, True)]
TAGS = ["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "wcag22aa"]


class Cdp:
    def __init__(self, ws_url):
        self.ws = websocket.create_connection(ws_url, timeout=60, suppress_origin=True)
        self.n = 0

    def call(self, method, **params):
        self.n += 1
        self.ws.send(json.dumps({"id": self.n, "method": method, "params": params}))
        while True:
            msg = json.loads(self.ws.recv())
            if msg.get("id") == self.n:
                if "error" in msg:
                    raise RuntimeError(msg["error"])
                return msg.get("result", {})

    def wait_event(self, name, timeout=30):
        end = time.time() + timeout
        while time.time() < end:
            self.ws.settimeout(max(0.1, end - time.time()))
            try:
                msg = json.loads(self.ws.recv())
            except websocket.WebSocketTimeoutException:
                break
            if msg.get("method") == name:
                return True
        return False


def wait_for_chrome(port, proc, seconds=60):
    """Waits until Chrome's debugging port answers (it starts slowly on shared build machines)."""
    end = time.time() + seconds
    while time.time() < end:
        if proc.poll() is not None:
            raise SystemExit("Chrome exited before it was ready (exit code %s)." % proc.returncode)
        try:
            urllib.request.urlopen(f"http://127.0.0.1:{port}/json/version", timeout=2).read()
            return
        except Exception:
            time.sleep(0.5)
    raise SystemExit("Chrome did not become ready within %s seconds." % seconds)


def get_axe():
    CACHE.mkdir(exist_ok=True)
    path = CACHE / "axe.min.js"
    if not path.exists():
        urllib.request.urlretrieve(AXE_URL, path)
    return path.read_text(encoding="utf-8")


def run_page(port, url, width, height, mobile, axe_src):
    tab = json.load(urllib.request.urlopen(urllib.request.Request(f"http://127.0.0.1:{port}/json/new?about:blank", method="PUT")))
    cdp = Cdp(tab["webSocketDebuggerUrl"])
    try:
        cdp.call("Page.enable")
        cdp.call("Emulation.setDeviceMetricsOverride", width=width, height=height, deviceScaleFactor=1, mobile=mobile)
        cdp.call("Page.navigate", url=url)
        cdp.wait_event("Page.loadEventFired")
        time.sleep(3)  # charts load from the JSON:API after the page loads
        cdp.call("Runtime.evaluate", expression=axe_src)
        expr = ("axe.run(document, {runOnly: {type: 'tag', values: %s}}).then(r => JSON.stringify({"
                "violations: r.violations.map(v => ({id: v.id, impact: v.impact, help: v.help, helpUrl: v.helpUrl, nodes: v.nodes.map(n => ({target: n.target.join(' '), summary: n.failureSummary}))})),"
                "passes: r.passes.length, incomplete: r.incomplete.map(v => ({id: v.id, help: v.help, count: v.nodes.length}))}))") % json.dumps(TAGS)
        res = cdp.call("Runtime.evaluate", expression=expr, awaitPromise=True, returnByValue=True)
        axe = json.loads(res["result"]["value"])
        over = cdp.call("Runtime.evaluate", returnByValue=True, expression=(
            "JSON.stringify({scrollWidth: document.documentElement.scrollWidth, clientWidth: document.documentElement.clientWidth,"
            "lang: document.documentElement.lang, title: document.title, h1: document.querySelectorAll('h1').length})"))
        page = json.loads(over["result"]["value"])
        # Keyboard: press Tab through the page and record controls with no visible focus indicator.
        cdp.call("Runtime.evaluate", expression="document.body.focus(); window.scrollTo(0,0)")
        hidden, seen, order, obscured = [], set(), [], []
        for _ in range(120):
            for t in ("keyDown", "keyUp"):
                cdp.call("Input.dispatchKeyEvent", type=t, key="Tab", code="Tab", windowsVirtualKeyCode=9)
            r = cdp.call("Runtime.evaluate", returnByValue=True, expression=(
                "(() => { const e = document.activeElement; if (!e || e === document.body) return null; e.scrollIntoView({block: 'nearest', inline: 'nearest', behavior: 'instant'}); const s = getComputedStyle(e);"
                "const b = e.getBoundingClientRect(); const hd = document.querySelector('header'); const sticky = hd && getComputedStyle(hd).position === 'sticky'; const cx = Math.min(Math.max(b.left + b.width / 2, 1), innerWidth - 1); const cy = Math.min(Math.max(b.top + Math.min(b.height / 2, 10), 1), innerHeight - 1); const topEl = document.elementFromPoint(cx, cy); const obscured = !!(b.width > 0 && b.height > 0 && topEl && !(topEl === e || e.contains(topEl) || topEl.contains(e))); return JSON.stringify({obscured: obscured, pos: Math.round(b.left + window.scrollX) + ',' + Math.round(b.top + window.scrollY), name: e.tagName.toLowerCase() + (e.id ? '#' + e.id : '') + ' ' + (e.textContent || e.value || e.getAttribute('aria-label') || '').trim().slice(0, 30),"
                "outline: s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) > 0, shadow: s.boxShadow !== 'none'}); })()"))
            v = r["result"].get("value")
            if not v:
                continue
            info = json.loads(v)
            key = info["pos"] + info["name"]
            if key in seen:
                break
            seen.add(key)
            order.append(info["name"])
            if not (info["outline"] or info["shadow"]):
                hidden.append(info["name"])
            if info.get("obscured"):
                obscured.append(info["name"])
        # Anchor jump: after the skip link target, the first heading must sit below any fixed header.
        cdp.call("Page.navigate", url=url.split("#")[0] + "#main-content")
        time.sleep(2)
        jump = cdp.call("Runtime.evaluate", returnByValue=True, expression=(
            "(() => { const hd = document.querySelector('header'); const h1 = document.querySelector('h1');"
            "const sticky = hd && getComputedStyle(hd).position === 'sticky';"
            "return JSON.stringify({covered: !!(sticky && h1 && h1.getBoundingClientRect().top < hd.getBoundingClientRect().bottom - 1 && h1.getBoundingClientRect().bottom > 0)}); })()"))
        cdp.call("Page.navigate", url=url)
        cdp.wait_event("Page.loadEventFired")
        time.sleep(2.5)
        ov = cdp.call("Runtime.evaluate", returnByValue=True, expression=(
            "JSON.stringify([...document.querySelectorAll('td, th')].filter(c => c.offsetParent !== null && getComputedStyle(c).display.startsWith('table')"
            " && [...c.children].some(k => k.getBoundingClientRect().right > c.getBoundingClientRect().right + 1 || k.scrollWidth > k.clientWidth + 1))"
            ".map(c => (c.getAttribute('data-label') || c.tagName) + ': ' + (c.textContent || '').trim().slice(0, 30)))"))
        page["cell_overflow"] = json.loads(ov["result"]["value"])
        page["keyboard"] = {"focus_stops": len(order), "no_visible_focus": hidden, "first_stops": order[:4],
                            "obscured_by_header": obscured, "anchor_jump_covered": json.loads(jump["result"]["value"])["covered"]}
        return axe, page
    finally:
        cdp.ws.close()
        urllib.request.urlopen(f"http://127.0.0.1:{port}/json/close/{tab['id']}")


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--base", default="https://camh-tracker.ddev.site")
    ap.add_argument("--port", type=int, default=9333)
    args = ap.parse_args()
    axe_src = get_axe()
    profile = tempfile.mkdtemp(prefix="axe-chrome-")
    chrome = subprocess.Popen([CHROME, "--headless=new", f"--remote-debugging-port={args.port}", f"--user-data-dir={profile}",
                               "--ignore-certificate-errors", "--disable-gpu", "--no-first-run", "--no-sandbox", "about:blank"],
                              stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    wait_for_chrome(args.port, chrome)
    results = []
    try:
        for lang in ("en", "es", "ar"):
            for name, path in PAGES:
                for vp, w, h, mobile in VIEWPORTS:
                    url = args.base + path.format(l=lang)
                    axe, page = run_page(args.port, url, w, h, mobile, axe_src)
                    results.append({"page": name, "lang": lang, "viewport": vp, "url": url, "axe": axe, "page_info": page})
                    print(f"{lang} {name:18} {vp:7} violations={len(axe['violations'])} overflow={page['scrollWidth'] > page['clientWidth']}")
    finally:
        chrome.terminate()
    out = ROOT / "evidence" / "accessibility"
    out.mkdir(parents=True, exist_ok=True)
    (out / "axe-results.json").write_text(json.dumps(results, indent=1), encoding="utf-8")
    lines = [f"# Automated accessibility and mobile check ({date.today().isoformat()})", "",
             "Tool: axe-core 4.10.2, rules WCAG 2.0, 2.1 and 2.2 level A and AA, in headless Chrome. Phone width 375 px, desktop 1280 px.",
             "Automated rules find only part of the problems. Keyboard, screen reader and zoom checks are separate.", "",
             "| Page | Language | Viewport | Violations | Horizontal scroll | One h1 | Page language | Tab stops | Stops without visible focus | Focus hidden under header | Anchor jump covered | Table cells overflowing |", "|---|---|---|---|---|---|---|---|---|---|---|---|"]
    for r in results:
        p = r["page_info"]
        lines.append(f"| {r['page']} | {r['lang']} | {r['viewport']} | {len(r['axe']['violations'])} | {'YES' if p['scrollWidth'] > p['clientWidth'] else 'no'} | {'yes' if p['h1'] == 1 else p['h1']} | {p['lang']} | {p['keyboard']['focus_stops']} | {len(p['keyboard']['no_visible_focus'])} | {len(p['keyboard']['obscured_by_header'])} | {'YES' if p['keyboard']['anchor_jump_covered'] else 'no'} | {len(p['cell_overflow'])} |")
    lines += ["", "First Tab stops (desktop, English commitments list): " + ", ".join(f"`{x}`" for x in results[0]["page_info"]["keyboard"]["first_stops"]), ""]
    bad = {x for r in results for x in r["page_info"]["keyboard"]["no_visible_focus"]}
    if bad:
        lines += ["Controls without a visible focus indicator: " + ", ".join(sorted(f"`{x}`" for x in bad)), ""]
    lines += ["", "## Violations", ""]
    seen = {}
    for r in results:
        for v in r["axe"]["violations"]:
            key = (v["id"], r["page"])
            seen.setdefault(key, {"v": v, "where": []})["where"].append(f"{r['lang']}/{r['viewport']}")
    if not seen:
        lines.append("None found by the automated rules.")
    for (vid, page), d in sorted(seen.items()):
        v = d["v"]
        lines.append(f"- **{vid}** ({v['impact']}) on {page} [{', '.join(d['where'])}]: {v['help']}. First element: `{v['nodes'][0]['target']}` ({len(v['nodes'])} element(s)). {v['helpUrl']}")
    (out / "axe-report.md").write_text("\n".join(lines) + "\n", encoding="utf-8")
    total = sum(len(r["axe"]["violations"]) for r in results)
    print(f"Report: {out / 'axe-report.md'} ({total} violation instances)")
    sys.exit(1 if total or any(r['page_info']['keyboard']['no_visible_focus'] or r['page_info']['keyboard']['obscured_by_header'] or r['page_info']['keyboard']['anchor_jump_covered'] or r['page_info']['cell_overflow'] for r in results) else 0)


if __name__ == "__main__":
    main()
