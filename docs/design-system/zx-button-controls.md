# zx-button-controls

Wrapper for groups of action buttons in dialogs, forms, toolbars. All button groups MUST use this component instead of manual flex/gap layout.

`ng-zxart/src/app/shared/ui/zx-button-controls/`

## Props

| Prop | Values | Default |
|---|---|---|
| `align` | `start` \| `end` \| `distribute` | `end` |

Uses `gap: var(--gap-md)` between buttons.

```html
<zx-button-controls align="end">
  <zx-button color="outlined">Cancel</zx-button>
  <zx-button color="primary">Save</zx-button>
</zx-button-controls>
```
