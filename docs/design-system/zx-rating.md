# zx-rating

Interactive 5-star rating component with hover preview and user vote display.

`ng-zxart/src/app/shared/components/rating/`

## Usage

```html
<zx-rating
  [overallRating]="3.7"
  [votesAmount]="42"
  [userRating]="4"
  (voted)="onVote($event)"
></zx-rating>
```

```typescript
import {RatingComponent} from '../shared/components/rating/rating.component';

@Component({
  standalone: true,
  imports: [RatingComponent],
})
```

## Inputs

| Input | Type | Default | Description |
|-------|------|---------|-------------|
| `overallRating` | `number` | `undefined` | Average rating (0–5). Controls the filled star width. |
| `votesAmount` | `number` | `undefined` | Total number of votes. Shown in the tooltip on hover. |
| `userRating` | `number` | `undefined` | User's own vote (1–5). Shown as a number next to the stars. Hidden when `undefined`. |

## Outputs

| Output | Type | Description |
|--------|------|-------------|
| `voted` | `EventEmitter<number>` | Emitted on star click or skip. Value is 1–5 for a star, 0 to remove vote. |

## Behavior

- Five stars rendered as two layers: a grey background row and a yellow filled overlay (`.interactive`) whose `width%` is derived from `overallRating / 5 * 100`.
- On hover, the filled layer animates to the hovered star position (hover preview).
- On `pointerleave`, reverts to `overallRating` fill.
- An **×** (skip) button to the left allows removing a vote — emits `voted(0)`.
- `userRating` is shown as a small number next to the stars when set.

## Star size

Responsive via `--star-size` CSS variable:

| Breakpoint | `--star-size` |
|------------|--------------|
| `> md` (desktop) | `18px` |
| `≤ md` (tablet) | `24px` |
| `≤ sm` (mobile) | `32px` |

## Tooltip

`zx-rating` manages its tooltip internally. On each `ngOnChanges`, it sets the tooltip text to the translated vote count string (`zx-vote.votes` i18n key with `{count: overallRating}`).

No external binding is needed — the tooltip appears automatically whenever the component renders with an `overallRating` value.

## Theme variables

| Variable | Description |
|----------|-------------|
| `--rating-user-vote-font-size` | Font size of the user vote number (default: `var(--font-sm)`) |
| `--date-color` | Color of unfilled stars and skip icon |
| `--icon-yellow` | Color of filled stars and user vote number |

## Notes

- Not intended to be used standalone for voting — wrap with business logic (e.g. `zx-vote` for legacy, or a feature component for new code).
- Uses `angular-svg-icon` for star and × icons (`star.svg`, `x.svg`).
