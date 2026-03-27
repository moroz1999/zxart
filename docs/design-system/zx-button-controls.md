# zx-button-controls

Wrapper for groups of action buttons in dialogs, forms, toolbars. All button groups MUST use this component instead of manual flex/gap layout.

`ng-zxart/src/app/shared/ui/zx-button-controls/`

## Props

| Prop | Values | Default | Description |
|---|---|---|---|
| `align` | `start` \| `end` \| `distribute` \| `fill` \| `full` | `end` | Button layout variant |
| `wrap` | `boolean` | `false` | Allow buttons to wrap to next line |

- **`start`** — buttons at minimum width, no wrap, left-aligned
- **`end`** — buttons at minimum width, no wrap, right-aligned
- **`distribute`** — buttons at minimum width, no wrap, space-between
- **`fill`** — buttons grow to fill the row equally (grid, 1fr each)
- **`full`** — each button 100% width, stacked vertically
- **`[wrap]="true"`** — adds `flex-wrap: wrap` to any `align` variant

Uses `gap: var(--gap-md)` between buttons.

```html
<zx-button-controls align="end">
  <zx-button color="outlined">Cancel</zx-button>
  <zx-button color="primary">Save</zx-button>
</zx-button-controls>

<zx-button-controls align="fill">
  <zx-button color="outlined">Previous</zx-button>
  <zx-button color="outlined">Next</zx-button>
</zx-button-controls>

<zx-button-controls align="full">
  <zx-button color="primary">Submit</zx-button>
  <zx-button color="outlined">Cancel</zx-button>
</zx-button-controls>
```
