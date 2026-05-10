# ZXArt Design System

A design system distilled from **[zxart.ee](https://zxart.ee)** — an online archive of ZX Spectrum art: graphics, chiptunes, demos, software prods, parties, authors and groups.

The system mirrors the **`ng-zxart/`** Angular 19 frontend (the only non-legacy UI in the repo). Older Smarty/LESS templates in `htdocs/` and `project/templates/` are explicitly out of scope — they are being phased out.

## Sources

| Source | Path |
|---|---|
| GitHub repo | https://github.com/moroz1999/zxart |
| Angular app | `ng-zxart/` (Angular 19, FSD architecture, standalone components) |
| Theme tokens | `ng-zxart/src/app/shared/theme/_*.theme.scss` |
| Design-system docs | `docs/design-system.md`, `docs/styles.md`, `docs/angular.md` |
| Domain docs | `docs/domain.md` (entities: picture, tune, prod, release, party, author, group, comment) |
| Icon set | `ng-zxart/src/assets/svg/*.svg` (38 SVGs — Material-style icons) |
| Logo + fonts | `htdocs/images/logo.png`, `htdocs/fonts/pt-sans.*.woff` |

> All assets in this design system are copies. Anything outside `ng-zxart/` is legacy and should not be reused.

## Index

- **`colors_and_type.css`** — full token set: color ramps, semantic colors, type scale, spacing, shadows, radii, animation. Light is default; add `class="dark-mode"` to flip.
- **`fonts/`** — PT Sans Regular + Bold (the body face from production).
- **`assets/`** — `logo.png`, `logo_og.png`, `favicon.ico`, `apple-touch-icon.png`, plus the full `assets/icons/` SVG icon set.
- **`_legacy/`** — verbatim copies of the source theme SCSS, kept for reference. Do **not** import directly.
- **`preview/`** — design-system cards (HTML) registered for the Design System tab.
- **`ui_kits/website/`** — recreations of the zxart.ee site: header, picture card, tune row, player, prod card, etc.
- **`SKILL.md`** — Agent Skill manifest. Read this if you're loading the system into Claude Code.

## What zxart.ee is

A multilingual (EN / RU / ES) catalog and player for the ZX Spectrum scene. Visitors browse, listen, vote, and download:

- **Pictures** — pixel graphics in native ZX formats (palette, border, rotation, isFlickering, isRealtime). Rendered with `image-rendering: pixelated`, 4:3 aspect.
- **Tunes** — chiptunes (AY, Beeper, Turbosound) playable in-browser. There's a sticky bottom **player** with a built-in radio.
- **Prods** (productions) — software entities: games, demos, intros, utilities. Each prod can have multiple **releases**.
- **Parties** — demoparties / competitions, with compos and placings (gold/silver/bronze medals on cards).
- **Authors / Groups** — people and crews. Authors have aliases; groups can be developers or publishers.
- **Comments + voting** — five-star rating + favourites/playlist on every item.

Core nav by entity type, with browser screens (catalogue with letter selector, sort, filter pickers) and detail pages.

## Content fundamentals

zxart.ee is a community archive, not a product page. Tone is **archival, factual, scene-savvy** — never marketing.

- **Voice:** third-person, neutral. Captions are short data labels: titles, author names, year, place, format. Verbs are imperative on controls only ("Play", "Add to playlist", "Download").
- **Casing:** Title Case for proper nouns (prod / party / author names — preserved verbatim, including `ALL CAPS` scene names like `RAZOR 1911` or `BYTEREALMS`). Sentence case for UI labels and translations.
- **Length:** copy is *terse*. A picture card shows: title, comma-separated authors, party + place, year. No descriptions. Long-form lives only in prod detail pages.
- **Person:** "you" rare; UI prefers verbs without subject ("Sign in", "Vote", "Add comment"). Translation files (`assets/i18n/en.json`, `ru.json`, `es.json`) are the source of truth.
- **Numbers / data:** years are 4-digit (`1987`); votes shown as star count + total voters. Placings are `1` / `2` / `3` inside circular medal badges.
- **Emoji:** **never used.** Iconography is SVG only (see ICONOGRAPHY).
- **Domain vocabulary:** keep scene terms as-is — *prod*, *release*, *compo*, *demoscene*, *chiptune*, *AY*, *Beeper*, *realtime*, *flickering*, *border*. Don't soften them.
- **Examples (real):**
  - Picture card: `"Eternal Flame" — Diver/4D, Chaos Constructions 2003, 1st place, 2003`
  - Tune row: `"Robocop" — Tim Follin · AY · 2:14`
  - Empty state: `"Nothing to show yet."` (translated)

## Visual foundations

The aesthetic is **utilitarian archive with one dab of brand color**. Think Wikipedia + Bandcamp — dense, fast, calm. Pixel art is the hero; the chrome stays out of its way.

### Color
- **Primary** is a single saturated blue (`--primary-500: #1a90ff` light / `#0068ca` dark). Used for links, primary buttons, and the focus state on the player.
- **Neutrals** are pure grays — no warm cast. The page is white in light mode (`--background-page: #f7f7f7`), near-black in dark (`#131313`).
- **Accents:** yellow (`--warning-400`, `#ffc933`) for medals, `link-alt`, and stars; red (`--danger-500`) only for destructive / error states.
- **Two themes are mandatory** — `.light-mode` and `.dark-mode`. Every component uses semantic vars (`--text-color`, `--surface`) so theme switching is automatic. Hardcoded hex outside the theme files is a blocker.
- **Imagery is unmodified ZX pixel art** — no filters, no grain, no overlays. The CSS `image-rendering: pixelated` rule preserves every dithered pixel.

### Typography
- **PT Sans** body face — humanist sans, slightly warm, designed for screens. Regular + Bold only; no italic, no other weights.
- Six font tiers: `--font-xs 11` / `sm 13` / `md 14` / `lg 18` / `xl 21` / `xxl 23`. **Whole pixels only**, no rem/em.
- Three heading levels (`h1`/`h2`/`h3`) — bold, tight line-height (1.2 → 1.3). Body is 1.5.
- No display fonts, no hand-lettering, no decorative type. The scene name on the logo is the only "branded" lettering.

### Spacing & layout
- **Strict 4px grid** (`--space-2..64`). Manual margins between layout siblings are forbidden — use `gap` on flex/grid or the `zx-stack` component.
- Header, content, player are **horizontally centered, max-width 1900px**. The page can be edge-to-edge above this — there is no fancy background.
- Sticky desktop header (`position: sticky; top: 0`); fixed on mobile (45px tall).
- Sticky bottom player covers chiptune playback while users browse.
- Dense lists, generous tap targets — buttons are `xs 24` / `sm 30` / `md 36` px tall.

### Surfaces (cards, panels)
- Cards use `<zx-panel>`: white surface in light, `--secondary-100` in dark.
- **Border-radius: small** — `--radius-md 3` / `--radius-lg 6` for buttons, panels. Picture cards alone use `--radius-xl 12`.
- **Borders are thin**: 1px `--border-secondary`. Always *subtle* — they delineate, never decorate.
- **Shadows are quiet**: `--shadow-sm` 1px / `--shadow-md` 4px / `--shadow-lg` 10px. No glows, no neon.
- No left-border-color accent cards. No glassmorphism. No big rounded blob containers.

### Backgrounds
- **No** decorative gradients, textures, illustrations, or photos behind UI. Pages are flat solid.
- The single visual flourish is the **logo image**: a small pixel-art `logo.png` in the header.
- Demoscene imagery is the *content*, not the chrome.

### Animation & interaction
- **Subtle, fast.** Allowed durations: `--animation-xs 100ms`, `--animation-sm 200ms`. State changes only.
- Allowed properties: `background-color`, `color`, `opacity`, `border-color`. Transitioning layout props (height, width, transform) is forbidden.
- `zx-button` has a **Material-style ink ripple** on press — the only "cute" interaction in the system. Ripple expands from the click point with a 560ms cubic-bezier; opacity fades to 0.
- Hover = slightly darker fill (e.g. `--primary-500` → `--primary-600`). Outlined / transparent buttons get a `--secondary-200` tint on hover.
- Press = no shrink, no shadow shift — just the ripple + the darker hover color staying.
- **No bounces, no springs, no parallax.** This is an archive.

### Focus & a11y
- `:focus-visible` shows a 2px outer box-shadow in `--border-focused` (near-black light / white dark). Never `outline: 0` without a replacement.
- All interactive controls go through `zx-button` (even links — pass `href`). Native `<button>` is forbidden in production code.

### Transparency / blur
- **Used sparingly:** `--overlay-bg: rgba(0,0,0,.45)` in light, `rgba(0,0,0,.65)` in dark, on dialog backdrops only.
- No frosted glass. No translucent navbars. No blur filters in components.

### Layout rules (enforced)
- Negative margins are **prohibited** (`calc(-1 * ...)` is a blocker).
- Wrapper `<div>`s are forbidden where `:host` works.
- Parent components style **only** the external contour of children (margin/min-width/flex-grow). Internal `padding`/`gap`/`font-size` is owned by the child.

## Iconography

ZXArt uses a small **Material-Symbols-style SVG set**, served as flat single-color SVGs and rendered via `angular-svg-icon` so they inherit `currentColor`.

- **Source:** `assets/icons/` (38 icons copied verbatim from `ng-zxart/src/assets/svg/`).
- **Style:** filled, rounded geometric, 24×24 viewBox, single path. Visual feel: Material Symbols Rounded — neutral, system-y, not playful.
- **Coverage:** transport (`play`, `pause`, `skip-next`, `skip-previous`, `stop`, `shuffle`, `repeat`, `repeat-one`), navigation (`menu`, `search`, `close`, `expand-more`, `expand-less`, `open-in-new`), entity (`music-note`, `image`, `videogame-asset`, `disc`, `photo-camera`), action (`favorite`, `favorite-border`, `star`, `download`, `chat`, `cart`, `check`, `cancel`), profile (`person`, `globe`, `theme`, `settings`).
- **Sizing:** **pixel sizes only**, set via component theme vars (e.g. `--zx-player-round-icon-size: 18px`). `em` for icon size is forbidden by the styles guide.
- **Color:** icons inherit `currentColor`. Yellow stars use `--icon-yellow` (`--warning-400`).
- **Logo:** `assets/logo.png` — small pixel-art "zx-art" wordmark, ~35px tall in production header.
- **Emoji:** **never** — flag any emoji usage as off-brand.
- **Unicode glyphs as icons:** never — use SVG.
- **Brand illustrations:** none. The brand has no mascot, no hero illustrations. Demoscene pixel art *is* the brand visual.

> **Note:** the source repo also ships two icon WOFF files (`icons.woff`, `svg_icons.woff`) used by the legacy Smarty templates. These are **not** used by `ng-zxart` and are not included here.

## How to use

1. Link `colors_and_type.css` once in `<head>`.
2. Add `class="light-mode"` (or `dark-mode`) to `<html>` or a top-level wrapper. Both modes inherit from `:root`.
3. Use semantic CSS variables — never hex. If a variable is missing, add it to a component theme block.
4. Use the SVGs from `assets/icons/`. To recolor: `<svg ... fill="currentColor">` and set `color:` on a parent.
5. For component recipes, see `ui_kits/website/index.html`.

## Caveats

- **Smarty/LESS legacy ignored** per user instruction. Anything in `htdocs/`, `project/templates/`, `project/css/public/` is excluded.
- Icon set is functional but not exhaustive — if a missing glyph is needed, prefer Material Symbols Rounded (filled) for consistency.
- The repo has no Figma file or formal brand guidelines doc; this README synthesizes the system from the SCSS theme files + `docs/design-system.md`.
