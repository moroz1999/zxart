# Dialogs (CDK Dialog)

All dialogs use Angular CDK `Dialog`. No Material dialogs. Every dialog MUST have a darkened backdrop.

## Rules

- **Always** pass `backdropClass: 'zx-dialog-backdrop'` (darkens background with `--overlay-bg`).
- **Always** pass a `panelClass` that styles the dialog panel (see variants below).
- `zx-mobile-nav-backdrop` is the only exception — used exclusively for the side-drawer nav.

## Size constraints (all dialog variants except nav drawer)

- **Desktop**: `max-height: 80vh`. Content scrolls internally within the dialog component.
- **Mobile** (`≤ lg`): fullscreen — `position: fixed; inset: 0; width/height: 100%`. Content scrolls internally.

These constraints are applied via CSS on `cdk-overlay-pane.<panelClass>` in `styles.scss`. Dialog components must handle their own internal scrolling (e.g., `overflow-y: auto` on a scrollable region).

## Panel class variants

| `panelClass` | Use case | Defined in |
|---|---|---|
| `zx-dialog` | Generic centered dialog (settings, selectors, config) | `styles.scss` |
| `zx-search-dialog` | Search: 560px desktop, fullscreen mobile | `styles.scss` |
| `zx-panel-dialog` | Panels (ratings, comments): 480px desktop, fullscreen mobile | `styles.scss` |
| `zx-mobile-nav-drawer` | Side-drawer nav only | `styles.scss` |

## Standard dialog call pattern

```typescript
this.dialog.open(MyDialogComponent, {
  panelClass: 'zx-dialog',
  backdropClass: 'zx-dialog-backdrop',
});
```

## Backdrop classes

| Class | Background | Used for |
|---|---|---|
| `zx-dialog-backdrop` | `var(--overlay-bg)` | All overlay dialogs |
| `zx-mobile-nav-backdrop` | `var(--overlay-bg)` | Mobile nav drawer only |
