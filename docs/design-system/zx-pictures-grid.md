# zxPicturesGrid

Attribute directive that applies a responsive CSS Grid layout for picture cards.
Eliminates the copy-paste `.pictures-grid` block that previously appeared in every
firstpage picture module.

## Location

`ng-zxart/src/app/shared/directives/pictures-grid.directive.ts`

CSS rules are defined globally in `ng-zxart/src/styles.scss` (`.pictures-grid` class).

## Layout

- `grid-template-columns: repeat(auto-fill, 320px)` — fills the row with as many 320 px columns as fit.
- `justify-content: space-between` — distributes columns evenly.
- `gap: var(--space-32) var(--space-16)` — row and column gaps.
- On `sm` breakpoint and below: collapses to a single full-width column.

## Usage

Apply the directive as an HTML attribute on the grid container element:

```html
<div zxPicturesGrid>
  <zx-picture-card
    *ngFor="let pic of items; let i = index"
    [picture]="pic"
    [galleryIndex]="i"
    [galleryId]="galleryId"
  ></zx-picture-card>
</div>
```

Import in your standalone component:

```typescript
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';

@Component({
  imports: [ZxPicturesGridDirective, ...],
})
```

## Used in

- `zx-fp-new-pictures`
- `zx-fp-best-pictures-of-month`
- `zx-fp-random-good-pictures`
- `zx-fp-unvoted-pictures`
