# Dialogs (CDK Dialog + zx-dialog component)

All dialogs use Angular CDK `Dialog` and the `zx-dialog` shell component. No Material dialogs.

## Rules

- **Always** pass `backdropClass: 'zx-dialog-backdrop'`.
- **Always** pass `panelClass: 'zx-dialog'`.
- Every dialog MUST use `<zx-dialog>` as the root element in its template.

## zx-dialog component

`shared/ui/zx-dialog/zx-dialog.component.ts`

Provides: sticky header (title + close button), scrollable body, sticky footer.

| Input | Type | Default | Description |
|---|---|---|---|
| `titleKey` | `string` | — | Translation key for the dialog title |
| `title` | `string` | — | Literal title string (use when title comes from data) |
| `showClose` | `boolean` | `true` | Show/hide the close button |
| `customHeader` | `boolean` | `false` | Replace default header with `[zxDialogHeader]` slot |
| `(closeClick)` | `EventEmitter` | — | Emitted when close button is clicked |

## Template structure

```html
<zx-dialog titleKey="my.translation.key" (closeClick)="close()">
  <!-- body content (scrollable) -->
  <my-content></my-content>

  <!-- optional sticky footer -->
  <div zxDialogFooter>
    <zx-button-controls align="end">
      <zx-button color="primary" (click)="save()">Save</zx-button>
    </zx-button-controls>
  </div>
</zx-dialog>
```

### Custom header (search, non-standard inputs)

Use `[customHeader]="true"` and the `[zxDialogHeader]` slot when the header is not a standard title+close row:

```html
<zx-dialog [customHeader]="true">
  <div zxDialogHeader class="my-input-row">
    <input ... />
    <zx-button (click)="close()">...</zx-button>
  </div>

  <!-- body and footer as usual -->
</zx-dialog>
```

The `[zxDialogHeader]` element must use `flex: 1` to fill the header width.

Footer slot: use the `zxDialogFooter` attribute on a wrapper element. The footer is automatically hidden if empty.

## Standard dialog call pattern

```typescript
this.dialog.open(MyDialogComponent, {
  panelClass: 'zx-dialog',
  backdropClass: 'zx-dialog-backdrop',
});
```

For dialogs that need a specific width, pass it in the config:

```typescript
this.dialog.open(MyDialogComponent, {
  panelClass: 'zx-dialog',
  backdropClass: 'zx-dialog-backdrop',
  width: '480px',
});
```

## Size constraints

- **Desktop**: `max-height: 80vh`. Body scrolls internally.
- **Mobile** (`≤ lg`): fullscreen — `position: fixed; inset: 0`. Body scrolls internally.

## Backdrop

| Class | Used for |
|---|---|
| `zx-dialog-backdrop` | All overlay dialogs |
| `zx-mobile-nav-backdrop` | Mobile nav drawer only |

## Theme variables

Defined in `shared/theme/_zx-dialog.theme.scss`:

| Variable | Description |
|---|---|
| `--zx-dialog-header-padding` | Header padding |
| `--zx-dialog-header-border` | Header bottom divider |
| `--zx-dialog-body-padding` | Body padding |
| `--zx-dialog-footer-padding` | Footer padding |
| `--zx-dialog-footer-border` | Footer top divider |
| `--zx-dialog-close-icon-size` | Close button icon size |
