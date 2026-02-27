# Breakpoints

## SCSS Breakpoints (vars.scss)

Defined in `ng-zxart/src/app/shared/vars.scss`:

| Name  | Value  | Description |
|-------|--------|-------------|
| `xs`  | 320px  | Lower bound — very small screens |
| `sm`  | 576px  | Mobile landscape / phone upper bound |
| `md`  | 768px  | Tablet |
| `lg`  | 992px  | Small desktop |
| `xl`  | 1200px | Desktop |
| `xxl` | 1400px | Wide screen |

### What counts as "mobile" in SCSS

- **Portrait (phone):** `@include media-breakpoint-down(sm)` → `max-width: 575.98px`
- **All mobile (portrait + landscape):** `@include media-breakpoint-down(md)` → `max-width: 767.98px`
- **Tablet:** `@include media-breakpoint-between(md, lg)` → `768px–991.98px`

Mixins from `breakpoints.scss`:

```scss
@use 'shared/breakpoints' as bp;

// Phone only (portrait)
@include bp.media-breakpoint-down(sm) { ... }

// Phone + tablet
@include bp.media-breakpoint-down(lg) { ... }

// Tablet only
@include bp.media-breakpoint-between(md, lg) { ... }

// md and above
@include bp.media-breakpoint-up(md) { ... }
```

---

## Angular CDK Breakpoints (TypeScript)

Angular CDK (`@angular/cdk/layout`) defines its own breakpoints based on **Material Design Device Metrics**,
which differ from the Bootstrap-style values used in SCSS.

Constants file: `ng-zxart/src/app/shared/breakpoints.ts`

### ZxBreakpoints — aligned with project SCSS breakpoints

| Constant | Media query | Maps to SCSS |
|----------|-------------|--------------|
| `ZxBreakpoints.Mobile` | `max-width: 575.98px` | `down(sm)` |
| `ZxBreakpoints.MobileLandscape` | `576px–767.98px` | `between(sm, md)` |
| `ZxBreakpoints.MobileAll` | `max-width: 767.98px` | `down(md)` |
| `ZxBreakpoints.Tablet` | `768px–991.98px` | `between(md, lg)` |
| `ZxBreakpoints.TabletDown` | `max-width: 991.98px` | `down(lg)` |
| `ZxBreakpoints.Desktop` | `min-width: 992px` | `up(lg)` |
| `ZxBreakpoints.SmallDesktop` | `992px–1199.98px` | `only(lg)` |
| `ZxBreakpoints.LargeDesktop` | `1200px–1399.98px` | `only(xl)` |
| `ZxBreakpoints.XLargeDesktop` | `min-width: 1400px` | `up(xxl)` |

### CdkBreakpoints — original Angular CDK values

> Use only when explicit compatibility with Angular Material / CDK components is required.
> For all other code, use `ZxBreakpoints`.

| Constant | Media query |
|----------|-------------|
| `CdkBreakpoints.XSmall` | `max-width: 599.98px` |
| `CdkBreakpoints.Small` | `600px–959.98px` |
| `CdkBreakpoints.Medium` | `960px–1279.98px` |
| `CdkBreakpoints.Large` | `1280px–1919.98px` |
| `CdkBreakpoints.XLarge` | `min-width: 1920px` |
| `CdkBreakpoints.Handset` | Phone (portrait < 600px + landscape < 960px) |
| `CdkBreakpoints.HandsetPortrait` | `max-width: 599.98px` portrait |
| `CdkBreakpoints.HandsetLandscape` | `max-width: 959.98px` landscape |
| `CdkBreakpoints.Tablet` | Tablet (portrait 600–840px + landscape 960–1280px) |

### Difference between SCSS and CDK

| Boundary | SCSS (`sm`) | CDK (`XSmall`) |
|----------|------------|----------------|
| Mobile portrait upper bound | 575.98px | 599.98px |

~24px gap. When using `BreakpointObserver` in TypeScript, use `ZxBreakpoints.Mobile` (575.98px)
so the logic matches CSS.

---

## Usage example

```typescript
import {BreakpointObserver} from '@angular/cdk/layout';
import {ZxBreakpoints} from '../shared/breakpoints';
import {takeUntilDestroyed} from '@angular/core/rxjs-interop';

@Component({...})
export class MyComponent {
  isMobile = false;

  constructor(private bp: BreakpointObserver) {
    bp.observe(ZxBreakpoints.MobileAll)
      .pipe(takeUntilDestroyed())
      .subscribe(state => {
        this.isMobile = state.matches;
      });
  }
}
```
