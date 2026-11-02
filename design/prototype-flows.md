# Prototype flows (Figma page "06 Prototype")

The prototype links the reference captures and wireframes into clickable journeys. It simulates navigation between screens only; it is not the working site. Journeys follow `docs/user-journeys.md`.

## Frames to copy onto page 06
Copy (Cmd+C) from the source page and paste (Cmd+V) onto "06 Prototype". Prototype links only work between frames on the same page.

| Frame | Copy from |
|---|---|
| Overview, English, Desktop 1280 | 04 Hi-fi desktop |
| Commitments list, English, Desktop 1280 | 04 Hi-fi desktop |
| Commitment detail, English, Desktop 1280 | 04 Hi-fi desktop |
| Country profile (Kenya), English, Desktop 1280 | 04 Hi-fi desktop |
| Indicators, English, Desktop 1280 | 04 Hi-fi desktop |
| Overview, Spanish, Desktop 1280 | 04 Hi-fi desktop |
| Overview, English, Mobile 375 | 05 Hi-fi tablet mobile |
| Commitments list, English, Mobile 375 | 05 Hi-fi tablet mobile |
| Mobile menu open, 375 | 03 Wireframes low-fi |

## Links to draw
Each link is an invisible rectangle (a hotspot) placed over the link text on the screen.

**Journey A: find a commitment (desktop)**
1. Overview: nav "Commitments" -> Commitments list
2. Overview: nav "Indicators" -> Indicators
3. Overview: "Kenya" in Countries at a glance -> Country profile (Kenya)
4. Overview: commitment title in Recent updates -> Commitment detail
5. Commitments list: commitment title in the table -> Commitment detail
6. Commitment detail: breadcrumb "Home" -> Overview
7. Country profile: breadcrumb "Home" -> Overview
8. Indicators: nav "Home" -> Overview

**Journey B: switch language**
9. Overview (English): "Espanol" in the header -> Overview (Spanish)
10. Overview (Spanish): "English" in the header -> Overview (English)

**Journey C: phone**
11. Overview mobile: menu button (top right) -> Mobile menu open
12. Mobile menu open: "Commitments" -> Commitments list mobile
13. Mobile menu open: "Close" button -> Overview mobile

## Settings for every link
Trigger **On click**, action **Navigate to**, animation **Instant**.

## Starting point and playback
Set the flow starting point at "Overview, English, Desktop 1280". For the tall captures, choose a Desktop device preset and scaling "Fit width" in the Prototype tab, so the whole page scrolls in the player.

Limits to state on the page: the Spanish journey covers one screen, and the other captures are images, so only the hotspots respond.
