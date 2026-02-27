# zx-button

Versatile button with multiple sizes and colors. Styled via `_zx-button.theme.scss`.

`ng-zxart/src/app/shared/ui/zx-button/`

## Props

| Prop | Values | Default |
|---|---|---|
| `color` | `primary` \| `secondary` \| `outlined` \| `transparent` \| `danger` | `primary` |
| `size` | `xs` \| `sm` \| `md` | `md` |
| `square` | boolean | `false` |

`square` removes horizontal padding, making width equal to height — for icon-only buttons.

## Colors

| Color | Use when |
|---|---|
| `primary` | Main call-to-action (Submit, Save, Confirm) |
| `secondary` | Important but not primary actions |
| `outlined` | Secondary actions, filters, Cancel — low visual weight |
| `transparent` | Many buttons in a row (pagination, letter navigation) |
| `danger` | Destructive actions only (Delete, Remove, Revoke) |
