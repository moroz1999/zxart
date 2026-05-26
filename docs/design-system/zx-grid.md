# zx-grid

CSS Grid-based layout container with a fixed number of columns at desktop breakpoints.

`ng-zxart/src/app/shared/ui/zx-grid/`

`align` and `justify` position items within their grid cells; they do not define item dimensions.

## Props

| Prop | Values | Default |
|---|---|---|
| `columns` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `1` |
| `rows` | `auto` \| `1` \| `2` \| `3` \| `4` | `auto` |
| `gap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `md` |
| `rowGap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `gap` |
| `columnGap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `gap` |
| `align` | `start` \| `center` \| `end` \| `stretch` | `stretch` |
| `justify` | `start` \| `center` \| `end` \| `stretch` | `stretch` |
