# Design tokens (v0.1)

An independent approximation inspired by publicly visible conventions of large international-organisation websites (a bright cyan-blue primary, clean sans-serif type, generous white space). Not an official palette and not derived from any internal design file. Related RTM rows: W1, S2, D1c, Q6.

Contrast ratios calculated against WCAG 2.1 (AA needs 4.5:1 for normal text, 3:1 for large text and UI components).

## Colour
| Token | Hex | Use | Contrast check |
|---|---|---|---|
| `color.brand.accent` | #1CABE2 | Decorative accents, chart fills, large shapes. **Never for text on white.** | 2.63:1 on white (fails text), dark text #1A1A1A on it 6.61:1 |
| `color.brand.primary` | #0B6E99 | Links, buttons, active states | 5.67:1 on white · 5.13:1 on tint |
| `color.brand.primary-dark` | #00577D | Hover, headings emphasis | 7.90:1 on white |
| `color.brand.tint` | #E8F6FC | Section backgrounds, selected rows | #1A1A1A on it 15.76:1 |
| `color.text.default` | #1A1A1A | Body text | 17.40:1 on white |
| `color.text.muted` | #595959 | Secondary text | 7.00:1 on white |
| `color.surface.default` | #FFFFFF | Page background | |
| `color.border.default` | #D0D5DD | Dividers, inputs | decorative; input borders need 3:1, so use `color.text.muted` for form field borders |
| `color.status.success` | #1B7F3B | Completed / On track | 5.07:1 on white |
| `color.status.warning` | #B35C00 | Delayed | 4.72:1 on white |
| `color.status.danger` | #B42318 | Errors | 6.57:1 on white |
| `color.status.neutral` | #475467 | Not started / No recent update | 7.69:1 on white |

Rule: status is never shown by colour alone; each status also has a text label and an icon. Status badges use a 2 px coloured border for the status colour, a near-black icon (`color.text.default`) and a near-black label, giving the highest text and icon contrast; colour is secondary.

## Typography
Font: Noto Sans (open-source, SIL Open Font License; supports Latin and Arabic scripts via the Noto family). Fallback: system sans-serif. Confirm licence terms before bundling.

| Style name (Figma) | Size / line-height | Weight | Use |
|---|---|---|---|
| `font/size/h1` | 36 / 44 px (mobile 28 / 36) | Bold 700 | Page title |
| `font/size/h2` | 28 / 36 px (mobile 24 / 32) | Bold 700 | Section title |
| `font/size/h3` | 20 / 28 px | Bold 700 | Sub-section, card title |
| `font/size/body` | 16 / 24 px | Regular 400 | Default text |
| `font/size/small` | 14 / 20 px | Regular 400 | Captions, source notes, table metadata |

Each style carries its own fixed line-height (body 24, small 20, h3 28, h2 36, h1 44 px). Line-height is part of the text style, not a separate style. Do not use text smaller than 14 px.

## Spacing
Spacing is applied through an 8 px layout grid on all frames. Steps are multiples of 8 px, with 4 px and 12 px as half-steps for tight components.

| Token | Value |
|---|---|
| `space.1` | 4 px (half-step) |
| `space.2` | 8 px |
| `space.3` | 12 px (half-step) |
| `space.4` | 16 px |
| `space.5` | 24 px |
| `space.6` | 32 px |
| `space.7` | 48 px |
| `space.8` | 64 px |

## Shape and focus
| Token | Value |
|---|---|
| `radius.sm / md` | 4 px / 8 px |
| `focus.ring` | 3 px solid #1A1A1A, outside the element (17.4:1 on white, 3.07:1 on the primary blue). In Figma it is an outside stroke; in CSS a 2 px offset may be added. |

## Implementation in Figma
- **Colour:** saved as colour styles named with the token path (e.g. `color/brand/primary`), shown on the Foundations page as a swatch sheet.
- **Typography:** saved as text styles named as in the table above.
- **Spacing:** documented here and applied with an 8 px layout grid; not stored as Figma variables.
- File colour profile: sRGB, so on-screen colours match the hex values above.
- In development these tokens become CSS custom properties and Bootstrap 5 Sass variables (Phase 5), keeping the same names.

## Form field states
Text input states: Default (1 px `color.text.muted` border), Focus (3 px `color.text.default` outside ring), Error (2 px `color.status.danger` border plus an error message in text, so the error is never shown by colour alone), Disabled (`color.border.default` fill). Every field has a visible label above it and optional helper text below; placeholder text is never used as the only label.

## Breakpoints (Bootstrap 5 aligned)
Mobile < 576 · Large phone / small tablet 576–767 · Tablet 768–991 · Desktop 992–1199 · Wide ≥ 1200. Figma frames: 375, 768, 1280.

## Direction
Layouts must work mirrored (right-to-left). Use logical terms (start/end) in design notes rather than left/right.

## Open items
- Verify licence terms of chosen font and any icon set before bundling.
- Chart palette (categorical, sequential) to be defined and contrast-tested in Phase 6.
