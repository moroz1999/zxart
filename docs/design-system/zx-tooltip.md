# zxTooltip

Attribute directive that shows a floating tooltip near the cursor.

**File:** `ng-zxart/src/app/shared/directives/tooltip/tooltip.directive.ts`

## Features

- Implemented as an **attribute directive**, not a wrapper component.
- Single fixed size.
- Floats **next to the cursor** (12px bottom-right), not anchored to the element.
- Fades in/out smoothly via `opacity` transition.
- Stays within the viewport — repositions left/up when near edges.
- Uses **Angular CDK `Overlay`** with `GlobalPositionStrategy` — rendered in the CDK overlay container, not in the host component's DOM.
- Overlay disposed on host `ngOnDestroy`.

**Internal component:** `TooltipOverlayComponent` (`tooltip-overlay.component.ts`) — rendered inside the CDK pane. Not for direct use.

## Usage

Always use translated strings — hardcoded text in templates and components is forbidden.

```html
<!-- In template via translate pipe -->
<span [zxTooltip]="'some.key' | translate">Hover me</span>
```

Import as a regular directive:

```typescript
import {TooltipDirective} from '../shared/directives/tooltip/tooltip.directive';

@Component({
  standalone: true,
  imports: [TooltipDirective],
})
```

Or apply to the component's **host element** via `hostDirectives` + inject for self-contained behavior:

```typescript
import {TooltipDirective} from '../../directives/tooltip/tooltip.directive';
import {TranslateService} from '@ngx-translate/core';

@Component({
  hostDirectives: [TooltipDirective],
  // TooltipDirective must NOT be in imports[] when used as hostDirective
})
export class MyComponent implements OnInit {
  private tooltip = inject(TooltipDirective);

  constructor(private translate: TranslateService) {}

  ngOnInit(): void {
    this.tooltip.text = this.translate.instant('my.tooltip-key', {count: this.value});
  }
}
```

This approach makes the tooltip self-contained — consumers of `<my-component>` get it automatically.

## Inputs

| Input | Type | Description |
|-------|------|-------------|
| `zxTooltip` | `string` | Tooltip text. Empty string disables the tooltip. |

## Theme

CSS variables defined in `_zx-tooltip.theme.scss`:

| Variable | Default | Description |
|----------|---------|-------------|
| `--zx-tooltip-bg` | `var(--neutral-800)` | Background |
| `--zx-tooltip-text` | `var(--white)` | Text color |
| `--zx-tooltip-padding` | `var(--space-4) var(--space-8)` | Inner padding |
| `--zx-tooltip-border-radius` | `var(--radius-sm)` | Border radius |
| `--zx-tooltip-font-size` | `var(--font-sm)` | Font size |
| `--zx-tooltip-shadow` | `var(--shadow-sm)` | Box shadow |

Dark mode (`.dark-mode`) overrides background to `--neutral-700`.

## Animation

Uses `transition: opacity var(--animation-sm) ease` — standard `200ms` speed.

## Limitations

- Not triggered on touch devices — hover only.
- Text content only — no HTML inside tooltip.
- No show delay support.
