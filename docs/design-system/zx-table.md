# zx-table

Table wrapper inside an elevated panel with `contentBleed`. Table rows go edge-to-edge while the title retains standard panel padding. Zebra striping uses two solid background colors (no transparency).

`ng-zxart/src/app/shared/ui/zx-table/`

## Props

| Prop | Values |
|---|---|
| `title` | string (optional heading above the table) |
| `titleLevel` | `h2` \| `h3` |
| `size` | `xs` \| `sm` \| `md` (default: `sm`) |

## Sizes

| Size | Font size | Cell padding | Typical use |
|---|---|---|---|
| `xs` | 13px | 4px 8px | Compact tables (ratings) |
| `sm` | 14px | 4px 8px | Tune lists |
| `md` | 16px | 8px 16px | Author browser, data tables |
