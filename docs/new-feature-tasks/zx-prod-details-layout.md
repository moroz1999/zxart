# Prod Details — Layout Redesign Plan

Source: claude.ai/design bundle `5K0sFKR1vbDrEZdXqwMq-A`, file `ui_kits/website/ProdPage.jsx`.

The design is a merged variant: hero/info from Variant A, releases table from Variant B, no right sidebar.

## Target structure

```
Breadcrumbs (caption, mono, muted)
─────────────────────────────────────────────────────
Hero grid  [360px cover | right info block]
  cover: 4:3, background-deep, radius-lg, screenshot count badge
  right:
    title (h1) + year (caption mono)
    alt title (caption muted)
    category chips + language chip + legal status chip
    rating row: big score + vote widget + votes count + added date
    authors / music / publisher / developer (caption)
    external links row
─────────────────────────────────────────────────────
Screenshots strip  (zx-panel)
  5-column × 2-row grid; cell [0] spans 2×2; last cell = "show more N"
─────────────────────────────────────────────────────
About  (zx-panel)
  description text
  tags row separated by dashed border-top
─────────────────────────────────────────────────────
Tabs:  Releases N  |  Media N  |  Links N  |  Discussion N
  Releases tab:
    filter bar (language + type filters, table/cards toggle)
    sortable table view (title, year, type, lang, platform, votes, plays, downloads, play button)
    card grid view (alternative)

  Media tab — sub-tabs:
    Articles + Maps  →  zx-prod-articles-section + zx-prod-maps-section
    Covers           →  zx-prod-inlays-section
    Music            →  zx-prod-music-section
    Graphics         →  zx-prod-pictures-section

  Links tab — sub-tabs:
    Series           →  zx-prod-series-section + zx-prod-series-prods-section
    Compilations     →  zx-prod-compilations-section + zx-prod-compilation-items-section

  Discussion tab:
    comment textarea + submit button
    zx-comments-list-view
    zx-ratings-list-view (votes history)
```

## Component mapping

All content sections already exist. The layout changes are structural only.

| Design element | Angular component | Action |
|---|---|---|
| Hero info block | `zx-prod-info-table` | Refactor: split into hero grid layout |
| Rating + vote | `zx-prod-vote-row` + `zx-item-controls` | Reuse as-is |
| Screenshots strip | `zx-prod-screenshots-section` | Reuse, may need layout adjustment |
| Description text | `zx-prod-description` | Reuse as-is |
| Tags | `zx-tags-quick-form-view` | Reuse as-is |
| Releases | `zx-prod-releases-section` | Move inside tab |
| Articles | `zx-prod-articles-section` | Move inside Media tab |
| Maps | `zx-prod-maps-section` | Move inside Media tab |
| Covers / inlays | `zx-prod-inlays-section` | Move inside Media tab |
| Music | `zx-prod-music-section` | Move inside Media tab |
| Graphics | `zx-prod-pictures-section` | Move inside Media tab |
| Series | `zx-prod-series-section` + `zx-prod-series-prods-section` | Move inside Links tab |
| Compilations | `zx-prod-compilations-section` + `zx-prod-compilation-items-section` | Move inside Links tab |
| Comments | `zx-comments-list-view` | Move inside Discussion tab |
| Vote history | `zx-ratings-list-view` | Move inside Discussion tab |
| Panel card | `zx-panel` | Reuse for each section card |
| Layout | `zx-stack` | Reuse for vertical stacking |

## What must be created

### 1. `zx-tabs` — new shared/ui component

Location: `ng-zxart/src/app/shared/ui/zx-tabs/`

Behavior:
- Tab bar: `border-bottom: 1px solid --secondary-200`; active tab: 2px bottom border in `--primary-500`, label color `--primary-600`; inactive: `--text-light-color`; hover: `--primary-600`
- Tab panels: only active panel is rendered (not hidden with CSS — use `*ngIf` or `@if`)
- Each tab has a label and an optional count badge (monospace, muted)
- Sub-tabs (Media, Links) use the same `zx-tabs` component nested inside the tab panel

Theme file: `_zx-tabs.theme.scss` with all colors via component-level variables.
No Material dependencies. CDK focus management is acceptable.

### 2. `zx-prod-details.component.html` — full restructure

Replace the flat `zx-stack spacing="xxl"` with the hero grid + tabs structure above.

### 3. `zx-prod-details.component.scss` — hero grid layout

```scss
.prod-details__hero {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: var(--space-20);
  align-items: start;
}
```

Responsive: collapse to single column at `≤ lg` breakpoint.

### 4. `_colors.theme.scss` — two new semantic variables (requires user approval)

```scss
--surface: var(--white);
--background-page: var(--secondary-50);
```

Dark mode overrides in `.dark-mode`:
```scss
--surface: var(--secondary-100);
--background-page: var(--secondary-50);
```

### 5. `zx-prod-details-skeleton` — update to match new layout

The existing skeleton must reflect the hero grid + tabs structure.

## What must NOT be created

- `zx-breadcrumbs` — out of scope, simple inline text is sufficient
- Any new section components — all content sections already exist
- Right sidebar — explicitly excluded by the design (comment in `ProdPage.jsx`)
