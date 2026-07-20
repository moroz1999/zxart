# zx-nav-chips

Horizontal strip of chips (A–Z letters, years, …) with one marked active. Each chip renders as a `zx-button`; the active chip uses the `primary` color, the rest `transparent`.

`ng-zxart/src/app/shared/ui/zx-nav-chips/`

A chip either **navigates** (when `href` is set — renders a link) or acts as an **in-page filter** (no `href` — renders a button that emits its `value` through `(chipSelect)`). Used by the author and group browsers and the authors dashboards (A–Z letters), the parties page (years), and the prods category (backend letter filter). Do not re-implement letter/year strips inline.

## Props

| Prop | Values | Default |
|---|---|---|
| `chips` | `ZxNavChip[]` | `[]` |
| `size` | `xs` \| `sm` \| `md` | `sm` |
| `square` | `boolean` | `false` |

`ZxNavChip` is `{ label: string; active: boolean; href?: string; value?: string }`.

## Output

| Event | Payload | Fires when |
|---|---|---|
| `chipSelect` | `string` | a non-link chip (no `href`) is clicked; emits its `value` (or `label`) |

## Letter helper

`buildLetterChips(basePath, activeLetter)` (`nav-chip.ts`) returns an A–Z `ZxNavChip[]` of link chips pointing at `${basePath}/<letter>`, marking `activeLetter` active (case-insensitive). Pass an empty `activeLetter` for landing pages where no letter is selected.
