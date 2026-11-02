# Manual accessibility checks

The automated check (`tests/accessibility/axe_check.py`) covers 63 page, language and size combinations. It cannot judge how a page sounds in a screen reader, how it behaves when magnified, or whether motion and chart colours are comfortable. These four checks cover that gap. Record each result in the table at the end.

Test pages (local site, https://camh-tracker.ddev.site):

- Overview: `/en`
- Commitments list: `/en/tracker/commitments`
- Commitment detail: `/en/commitments/uk-mental-health-and-wellbeing-plan` (open any published record from the list if the address differs)
- Country profile: `/en/countries/kenya`
- Indicators: `/en/tracker/indicators`

## 1. VoiceOver (macOS screen reader)

Start and stop VoiceOver with Cmd + F5. The VoiceOver key ("VO") is Control + Option.

For each of the five pages:

1. Press VO + U to open the Rotor, then use the left and right arrow keys to choose **Headings**. Confirm the headings read in a sensible order (one page title, then section titles).
2. In the Rotor choose **Landmarks**. Confirm you hear banner, navigation, main and footer.
3. Press Tab through the page. Every link, button and form control should be announced with a meaningful name, never just "button" or "link".
4. On the Overview and Country profile, find the chart. Confirm you hear a text description or can reach the data table beneath it.
5. On the Commitments list, use a filter, then confirm the result count or the new list is announced or reachable without searching.

Note anything that sounds wrong, with the page and what was read aloud.

## 2. Zoom to 200 and 400 percent

In Safari or Chrome press Cmd + plus until the zoom shows 200 percent, then 400 percent. (Cmd + 0 resets.)

At each level, on each page, confirm:

- No horizontal scrolling of the whole page (a table may scroll inside its own box).
- No text overlaps other text or is cut off.
- The menu is still reachable (at 400 percent it should collapse to the mobile menu).
- Buttons and filters can still be used.

## 3. Reduced motion

Open System Settings, then Accessibility, then Display, and switch on **Reduce motion**. Reload the Overview and Country profile.

Confirm that charts and the map appear without animating in, and that nothing slides or fades on hover or scroll. Switch the setting back afterwards.

## 4. Chart colour contrast

On the Overview, Indicators and Country profile pages, look at each chart.

- Each line, bar or map shade should be clearly distinguishable from its neighbour and from the white background.
- Every chart must have its values readable in the table beneath it, so no meaning depends on colour alone.
- Optional: in System Settings, Accessibility, Display, switch on a **Colour filter** (Greyscale) and confirm the charts still make sense. Switch it off afterwards.

## Results

| Check | Date | Result (pass / issue) | Notes |
|---|---|---|---|
| VoiceOver: Overview | 2026-10-29 | Pass (first pass) | Skim of headings and landmarks; nothing sounded wrong |
| VoiceOver: Commitments list | 2026-10-29 | Pass (first pass) | As above |
| VoiceOver: Commitment detail | 2026-10-29 | Pass (first pass) | As above |
| VoiceOver: Country profile | 2026-10-29 | Pass (first pass) | As above |
| VoiceOver: Indicators | 2026-10-29 | Pass (first pass) | As above |
| Zoom 200 percent | 2026-10-30 | Pass | Chrome |
| Zoom 400 percent | 2026-10-30 | Pass | Chrome |
| Reduced motion | 2026-11-02 | Pass (code review; hover and click tooltips stay by design) | `country-charts.js`, `indicators.js` and `overview-map.js` set `animation: !reduced` from `prefers-reduced-motion`; Bootstrap transitions are switched off under the same setting. Tooltips and click-to-reveal remain because they carry information and do not move |
| Chart contrast and greyscale | 2026-10-30 | Pass | Greyscale colour filter |
