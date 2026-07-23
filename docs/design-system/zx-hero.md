# zx-hero

`zx-hero` is the panel every entity detail page opens with. It owns the
`media | body` grid, the body rhythm and the full-width action bar rail, so all
entity pages share one container.

`ng-zxart/src/app/shared/ui/zx-hero/`

## Slot order

The page-level `<h1 appHeading="display" zxPageHeader>` stays **above** the hero
in `zx-page-layout`. The hero carries the entity's own title. Body slots are
placed in this fixed order and omitted when the entity has no such data:

| # | Slot | Component |
|---|---|---|
| — | media | entity-specific (avatar, logo, viewer, player, video, screenshot) |
| 1 | identification | `zx-hero-title` |
| 2 | facts | `zx-facts` + `zx-fact` |
| 3 | technical | `zx-chip` with `mono` / `mono-outline` |
| 4 | credits | `zx-credits-row` (`zx-prod-people-row` for prod/release DTOs) |
| 5 | provenance | `zx-callout` wrapping `zx-party-provenance` or `zx-prod-context`; the release uses `zx-release-parent-anchor`, which draws its own callout |
| 6 | service rows | `zx-meta-row`, with `zx-ext-links` for all outbound links |
| 7 | added by | `zx-added-by` |
| bar | actions | `zx-hero-bar` in the `zxHeroBar` slot |

The overall rating is rendered as the **first counter** of the action bar;
stars belong to the vote widget only. `zx-rating-strip` is used on the author
page, where several role ratings must be told apart.

## zx-hero

| Input | Values | Default |
|---|---|---|
| `media` | `none` \| `auto` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` | `auto` |

`media` selects the width of the media column: `xs` avatars, `sm` party logos,
`md` release screenshots, `lg` the tune player, `xl` the prod video, `auto` a
content-sized column, `none` a single-column hero. The grid collapses to one
column below the `md` breakpoint. Empty media and bar slots collapse.

```html
<zx-hero media="xs">
  <div zxHeroMedia>…</div>

  <zx-hero-title [title]="core.title" [entityId]="core.id">
    <zx-chip …></zx-chip>
    <zx-entity-editing-controls zxHeroEdit …></zx-entity-editing-controls>
  </zx-hero-title>

  <zx-facts>
    <zx-fact *ngIf="core.realName">{{ core.realName }}</zx-fact>
    <zx-fact><zx-location [city]="…" [country]="…"></zx-location></zx-fact>
  </zx-facts>

  <zx-hero-bar zxHeroBar>
    <zx-item-controls …></zx-item-controls>
    <zx-counters zxHeroBarCounters [items]="counters"></zx-counters>
    <zx-button-controls zxHeroBarActions align="end">…</zx-button-controls>
  </zx-hero-bar>
</zx-hero>
```

## zx-hero-title

Identification row with a fixed order: title, `#id` badge, year, projected type
badges and chips, edit control last.

| Input | Type | Notes |
|---|---|---|
| `title` | `string` | required; the entity's own title, rendered as a non-heading |
| `entityId` | `number \| null` | renders the `#id` badge |
| `year` | `number \| string \| null` | omitted when empty or `0` |

The default slot takes badges and chips; the `[zxHeroEdit]` slot always renders
last and holds the entity's editing controls.

## zx-hero-bar

Three zones on one line: vote controls (default slot),
`[zxHeroBarCounters]`, and `[zxHeroBarActions]` pushed to the trailing edge.
Empty zones collapse.

## Supporting components

- `zx-facts` / `zx-fact` — wrapping identity line; `zx-fact` draws its own
  leading `·`, so conditionally rendered facts never leave a dangling separator.
- `zx-location` — pin icon plus the linked city and country.
- `zx-counters` — `items: {value, label}[]` rendered as `**N** word`.
- `zx-meta-row` — right-aligned label column plus projected content; stacks on
  narrow viewports. The component adds the colon, so pass a bare label.
- `zx-ext-links` — the single rendering of outbound links: label plus an
  `open-in-new` icon. Always used inside a `zx-meta-row`.
- `zx-credits-row` — `groups: {label, people, note?}[]` rendered as
  `Role: names · Role: names`. Groups with no people are dropped.
- `zx-party-provenance` — placement medal, party link and compo, projected into
  a `zx-callout`.

## Technical chips

Technical facts use `zx-chip` variants instead of ad-hoc markup:

| Variant | Use for |
|---|---|
| `mono` | catalogued identifiers: hardware, platform, chip, abbreviation, year range, version |
| `mono-outline` | descriptive parameters: file format, resolution, duration |
