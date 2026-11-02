# zx-list-item

A row of a list the user picks from: a title, an optional second line, and the row's own actions on the right.

`ng-zxart/src/app/shared/ui/zx-list-item/`

## Props

| Prop | Values | Default |
|---|---|---|
| `title` | string | `''` |
| `subtitle` | string | `''` |
| `active` | boolean | `false` |

Output `picked` fires when the row is clicked. Projected content (actions such as a delete button, or a spinner) sits at the right end of the row, outside the clickable area.

## Rules

The row is a native `<button>` — its picking contract needs one; this is the documented exception to the `zx-button` rule. Do not put a `zx-button` around it or give the projected actions their own row click.

Both lines are cut with an ellipsis; the row never wraps. `active` marks the item currently open and sets `aria-current`.
