---
name: The Axis of Stevil Show
description: The axisofstevil.com archive read aloud as a deadpan quarterly earnings call; its investor relations site, built on the archive's own sheet and told apart by slate.
colors:
  stock: "#e6e3dc"
  stock-light: "#f7f4ee"
  ink: "#111111"
  muted: "#5a636b"
  line: "rgba(17, 17, 17, 0.13)"
  major: "rgba(17, 17, 17, 0.16)"
  slate: "#3f4a54"
  slate-deep: "#343d46"
  on-slate: "#eef0f1"
  on-slate-soft: "#c6ccd1"
  red: "#c73120"
  red-ink: "#c01300"
  red-on-dark: "#ffa79b"
typography:
  display:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "clamp(3rem, calc(var(--col) * 1.02), 6rem)"
    fontWeight: 700
    lineHeight: 0.88
    letterSpacing: "0"
  headline:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "clamp(2.25rem, calc(var(--col) * 0.5), 4rem)"
    fontWeight: 700
    lineHeight: 0.98
    letterSpacing: "0.01em"
  title:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "clamp(1.375rem, calc(var(--col) * 0.24), 2rem)"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "0.02em"
  figure:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "clamp(1.75rem, calc(var(--col) * 0.32), 2.75rem)"
    fontWeight: 600
    lineHeight: 1
    fontFeature: "tnum"
  body:
    fontFamily: "Source Serif 4, Iowan Old Style, Georgia, serif"
    fontSize: "1.0625rem"
    fontWeight: 400
    lineHeight: 1.6
    fontFeature: "tnum"
  body-lede:
    fontFamily: "Source Serif 4, Iowan Old Style, Georgia, serif"
    fontSize: "1.1875rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 600
    lineHeight: 1
    letterSpacing: "0.14em"
  caption:
    fontFamily: "Jost, Futura, sans-serif"
    fontSize: "0.8125rem"
    fontWeight: 500
    lineHeight: 1
    letterSpacing: "0.16em"
rounded:
  none: "0"
  disc: "50%"
spacing:
  margin: "24px"
  margin-phone: "16px"
  col: "var(--col)"
  half: "var(--half)"
  pad: "var(--pad)"
components:
  plate:
    backgroundColor: "{colors.stock}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    padding: "var(--pad) var(--pad) calc(var(--pad) + 2rem)"
  plate-lifted:
    backgroundColor: "{colors.stock-light}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
  call-plate:
    backgroundColor: "{colors.slate}"
    textColor: "{colors.on-slate}"
    rounded: "{rounded.none}"
    padding: "var(--pad) var(--pad) calc(var(--half) * 2 + var(--pad))"
  play-disc:
    backgroundColor: "{colors.red}"
    textColor: "#ffffff"
    rounded: "{rounded.disc}"
    size: "var(--half)"
  play-disc-hover:
    backgroundColor: "{colors.red-ink}"
  play-small:
    backgroundColor: "{colors.ink}"
    textColor: "#ffffff"
    rounded: "{rounded.disc}"
    size: "36px"
  play-small-current:
    backgroundColor: "{colors.red}"
  exhibit-tab:
    backgroundColor: "{colors.stock-light}"
    textColor: "{colors.ink}"
    rounded: "{rounded.none}"
    size: "max(44px, calc(var(--half) * 0.8))"
  exhibit-tab-active:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.stock-light}"
  agenda-item-current:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.stock-light}"
  dock:
    backgroundColor: "{colors.slate-deep}"
    textColor: "{colors.on-slate}"
    rounded: "{rounded.none}"
  tooltip:
    backgroundColor: "{colors.ink}"
    textColor: "{colors.stock-light}"
    rounded: "{rounded.none}"
    padding: "6px 10px"
---

# Design System: The Axis of Stevil Show

## Overview

**Creative North Star: "The Investor Relations Annex"**

The show's site is built on axisofstevil.com's own sheet: the same whole-pixel 12-column grid drawn on the stock in half-column squares, the same keylined plates captioned at their feet, the same Jost caps over Source Serif 4, the same stock and ink, the archive's type sizes. It is a sibling annex of the archive's annual report, never a copy of it. Two things tell it apart at a glance: **slate**, which leads where the archive's red leads (the call plate and the dock), and the show's lockup (the archive's symbol-and-name file, `axis-wordmark.svg`, with "Show" set after it) everywhere the archive carries its name.

The world is played straight: an earnest, well-made investor relations site for a mock-official earnings call. Density is that of a printed report: plates sharing edges, ruled indexes, tabular figures. The joke lives in the copy, never in the presentation.

Source of truth: `source/assets/site.css` (tokens on `:root`), `source/assets/snap.js` (the archive's grid snapping, copied unchanged and inlined before first paint), the Blade layouts and partials, and `source/assets/site.js` (the call's behaviour).

**Key Characteristics:**
- A visible half-module grid on the stock; plates snap to it.
- Plates with 1px ink keylines that share edges; a caption (`Plate N · Name`) at each plate's foot.
- Slate for the call and the dock; red only for what is live.
- The exhibit front and centre beside the call, with no heading.
- The site speaks the call's language: calls, call summary, the current call, current topic.

## Colors

Warm grey report stock and ink, one slate for the show, one red for the live moment.

### Primary
- **Call Slate** (#3f4a54): the call plate (the archive's cover in slate, its grid continuing across it in white at 10%), `::selection`, `theme-color`, link hover, the copied state.
- **Dock Slate** (#343d46): the dock's ground only.

### Secondary
- **Live Red** (#c73120): the live dot while the call plays (before play and on hold it waits in soft slate ink), the play disc, the chapter now speaking (timeline and dock-track fill), the small play button of the call now playing. **Red Ink** (#c01300) is its hover. **Red on Dark** (#ffa79b): the current agenda row's time and the "Current topic" label while playing.

### Neutral
- **Report Stock** (#e6e3dc): the page ground and the default plate.
- **Lifted Stock** (#f7f4ee): running head, colophon, imprint band, exhibit plate, letter, current call, notes, subscribe; the call title on slate.
- **Ink** (#111111): text, keylines, the pressed tab, the current agenda row, tooltips.
- **Muted** (#5a636b): captions, labels, times, finished chapters, upcoming calls.
- **Grid Line** (rgba(17,17,17,0.13)) draws the sheet; **Major Rule** (rgba(17,17,17,0.16)) rules index rows and divides the running head.
- **On Slate** (#eef0f1) and **On Slate Soft** (#c6ccd1): text on slate; figure labels and secondary dock text.

### Named Rules
**The Live-Only Red Rule.** Red marks only what is live: the line, the play disc, the chapter now speaking. No red headings, rules, links or decoration.

**The Slate Leads Rule.** Slate is the show's colour and holds exactly two surfaces: the call plate and the dock. The running head and colophon follow the archive on lifted stock.

## Typography

**Display Font:** Jost (with Futura, sans-serif), self-hosted variable 100–900.
**Body Font:** Source Serif 4 (with Iowan Old Style, Georgia), roman and italic.

**Character:** the archive's pairing: geometric caps for titles, figures and labels; a sturdy book serif for anything read.

### Hierarchy
- **Display** (700, clamp(3rem, col×1.02, 6rem), 0.88, uppercase): the call title. The home cover's "Earnings Calls" runs clamp(3rem, col×1.12, 6.25rem) at 0.86.
- **Headline** (700, clamp(2.25rem, col×0.5, 4rem), 0.98, uppercase): page titles and the current call's title. The letter title runs clamp(2rem, col×0.5, 3.25rem).
- **Title** (700, clamp(1.375rem, col×0.24, 2rem), 1, uppercase): section heads (Agenda, Call summary, Prepared remarks, The calls, Subscribe, Notes).
- **Figure** (600, clamp(1.75rem, col×0.32, 2.75rem), tabular): Elapsed and Length on the call plate; the letter's three figures at 700, up to 3.75rem.
- **Body** (400, 1.0625rem/1.6): the transcript and general text. **Lede** (1.1875rem): the call summary, letter, ledes; 62–66ch.
- **Label** (500–600, 0.75–0.8125rem, 0.12–0.16em, uppercase): nav, figure and fact labels, exhibit numbers, call status.
- **Caption** (500, 0.8125rem, 0.16em, uppercase, muted): plate captions.

### Named Rules
**The Tabular Clock Rule.** Every time and count uses tabular figures; live clocks reserve 5ch so they never shift as they tick.

**The Two Voices Rule.** Jost speaks for the report (titles, figures, labels, indexes); Source Serif speaks for the CFO (summary, letter, remarks). The one serif value among the figures is "Current topic", the line being spoken.

## Layout

**The sheet.** `.report` is `--sheet` wide: 12 columns, max 1680px, 24px margins (4 columns and 16px at ≤700px). `snap.js` sets `--col` to a whole even pixel, so `--half` (half a column, one grid square) is whole; `--pad` is a quarter column (14px on phones). The body draws the grid in `--line` at `--half` squares from `--sheet-left`. Sections stack a `--half` apart. `[data-snap]` blocks round their height up to whole half-modules; `[data-fill]` blocks (the first viewport) also grow to the last grid line in the viewport above 1100px.

**Spreads.** First viewport (`.fold-top`): two six-column plates, at least 10 half-modules tall. Episode: call plate beside exhibit plate, then a 9|3 spread (record | notes). Home: cover | letter; 8|4 current call | next call; 9|3 the run | subscribe. About: 9|3 record | notes.

**The record.** Above 1300px the call summary and agenda sit side by side, summary first; narrower they stack, agenda first. Prepared remarks put each part's time and title in a two-column margin. The notes column is sticky at `--half` and holds the facts; a small copy of the current exhibit unfolds in at its top (grid-row 0fr → 1fr, 0.5s) once the exhibit plate scrolls away, and a click returns to it.

**Breakpoints.** ≤1100px: every spread stacks (call over exhibit, notes under record), the run goes to two columns, the breadcrumb hides. ≤700px: four columns, the nav wraps under the lockup, the archive link hides, Length leaves the call figures, the run is one column, remarks' margin stacks above.

## Elevation & Depth

Flat. Depth is drawn, never cast: keylines, lifted stock and slate do the layering. The one ring of light is the live dot's pulse while playing.

### Shadow Vocabulary
- **Keyline** (`box-shadow: inset 1px 1px 0 var(--edge, var(--ink)), 1px 1px 0 var(--edge, var(--ink))`): every plate, the imprint band, exhibit tabs. Left and top inside, right and bottom outside, so neighbours share one line.
- **Picture keyline** (`::after { box-shadow: inset 0 0 0 1px var(--ink) }`): drawn over exhibits, which run to their frame's edges.
- **Dock keyline** (`inset 0 1px 0 var(--ink)`): the dock's top edge.
- **Live pulse** (`@keyframes pulse`, a 0 → 10px ring in red 0.65 → 0, 1.6s): the live dot while the call plays.

### Named Rules
**The Keyline Rule.** Plates are separated by shared 1px ink keylines on the grid, never by gaps, cards or shadows.

## Shapes

Square everything: plates, tabs, the dock, codes, tooltips, the close button. The only round forms are the play discs, the live dot and the saved-place ring (a 3px conic arc around a play button: ink on stock, lifted stock on slate; a saved place is not live, so never red). Indexes are ruled: 2px ink on top, 1px `--major` between rows. Exhibits sit on the archive's printed dot (1px ink dots at 9%, every 6px) where the picture is narrower than its frame, never on flat paper.

## Components

### Plate
The unit of the sheet: stock or lifted stock, keylined, padded `--pad` with 2rem at the foot for its caption, `Plate N · Name`, numbered per page.

### Call plate
Slate, the grid continuing across it in white at 10%. In order: the status (live dot; "On the line", then "Live" or "On hold"); the chapter timeline (half a grid square tall, one segment per chapter in proportion with 3px breaks, filling with stock as it plays, the current one filling red; each a seek button with a tooltip); the title in Display on lifted stock; a rule, then the play disc beside **Elapsed / Length / Current topic**; the imprint band (two half-modules of keylined lifted stock: the lockup left, quarter · part and the caption right). The home cover is the same plate with "Earnings Calls" and a calls-per-quarter chart (released in solid stock, scheduled in outline).

### Exhibit plate
Lifted stock, no visible heading (a visually-hidden "Exhibits"). The picture large on the printed dot, keylined over it; the caption gives "Exhibit N of M" in label caps, the title, and a link to the work on axisofstevil.com. Numbered square tabs below (≥44px, keylined; pressed in ink). A chapter with no exhibit shows its own picture as "No exhibit". It turns between exhibits (`turn`, 0.45s). It follows the call; a tab seeks to its chapter.

### Play buttons
- **Play disc:** a red circle, `--half` (52–88px), white glyph at 38%; hover red ink, scale 1.04. Play, pause and resume glyphs.
- **Small play:** a 36px ink circle in lists; slate on hover, red while its call plays. On lists a press starts the call in the dock, then navigates to the call's page.
- **Saved place:** a resume glyph and a progress ring (heard share as a conic arc) when the saved place lies between the end of the Welcome and the start of the forward-looking statements; outside that span, a plain play.

### Agenda and indexes
The agenda is a ruled list, one row per chapter: time, title, exhibit ("Ex. 3"). Hover lifts the row to lifted stock; finished titles go muted; the current row inverts to ink, its time in red-on-dark. The run is an index of quarters in CSS columns (3, 2, 1), each a ruled list of play · part · title · length, upcoming calls muted with their date. Facts are ruled label | value rows.

### Thumb index (the calls page)
The run beside a slate column a column and a half wide, as the archive's division index: the ten quarters, Q1 '05 to Q2 '07, under their years, sticky in view. A quarter with a call (released or scheduled) is a link; the rest are muted; the quarter holding the next call carries a 3px red edge. Each quarter's anchor names its quarter and year in full (`#q2-2005`), and an episode's quarter in its Notes links there. The quarter reached lifts onto lifted stock, ruled round 12px outside its own lines so nothing moves: by `:target` for the index's own jumps, by `.is-target` (site.js) after a visit from another page, which keeps the call playing.

### Running head and colophon
Both follow the archive: lifted-stock plates a half-module tall. The running head holds the lockup, the breadcrumb in Title caps after a `--major` divider, and the nav in label caps (current page underlined 2px ink, hover 2px major; "The works axisofstevil.com" set off by a divider). The colophon holds the lockup, the parody and AI disclosure in muted Jost, and the links.

### Dock
Fixed at the foot, slate-deep with an ink top keyline, rising in once (`rise`, 0.45s). A 48px play disc; the call's label, title and current chapter; a chaptered track (14px, a segment per chapter in proportion with 2px breaks, stock fill, the current one red, tooltips above); elapsed and length in tabular figures; a square close button. Kept across pages by Turbo; plays on through the run in order.

### Tooltip
Any `[data-tip]`: an ink box, lifted-stock Jost 0.8125rem, below its target (above in the dock), pinned to the edge for first and last segments; fades in on hover or focus.

## Do's and Don'ts

### Do:
- **Do** build every new section as a keylined plate on the sheet, whole columns wide, `data-snap`, with a `Plate N · Name` caption at its foot.
- **Do** resonate with the archive but never be confused with it: carry the show's lockup where the archive carries its name, and keep slate on the call and the dock.
- **Do** put the exhibit front and centre beside the call, with no heading over it.
- **Do** present chapters as a list (the agenda), and on the timelines in proportion, each named and seekable.
- **Do** say "Current topic", "Call summary", "the current call", "the next call": the site speaks the call's language; "show" and "episode" belong to the feed and tooling.
- **Do** use tabular figures for every clock and count.

### Don't:
- **Don't** use red for anything that is not live.
- **Don't** use rounded cards, drop shadows or gaps between plates; neighbours share a keyline.
- **Don't** set the archive's wordmark without "Show", or put slate or red in the running head; nobody should wonder which site they are on.
- **Don't** show a picture on flat paper; exhibits sit on the printed dot.
- **Don't** add motion beyond the call's own: the live pulse, the fills, the exhibit turning, the dock rising, the small exhibit unfolding. All of it stops under reduced motion.
