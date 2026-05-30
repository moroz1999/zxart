# zx-grid

CSS Grid-based layout container with a configurable number of columns per breakpoint.

`ng-zxart/src/app/shared/ui/zx-grid/`

`align` and `justify` position items within their grid cells; they do not define item dimensions.

## Props

| Prop | Values | Default |
|---|---|---|
| `desktopColumns` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `1` |
| `tabletColumns` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `desktopColumns` |
| `mobileColumns` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `tabletColumns` |
| `rows` | `auto` \| `1` \| `2` \| `3` \| `4` | `auto` |
| `gap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `md` |
| `rowGap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `gap` |
| `columnGap` | `none` \| `xs` \| `sm` \| `md` \| `lg` \| `xl` \| `xxl` | `gap` |
| `align` | `start` \| `center` \| `end` \| `stretch` | `stretch` |
| `justify` | `start` \| `center` \| `end` \| `stretch` | `stretch` |

## zxGridItem directive

`ng-zxart/src/app/shared/ui/zx-grid/zx-grid-item.directive.ts`

Apply to a direct child of `zx-grid` to make it span multiple columns.
Styles are defined in `zx-grid-item.directive.scss` and loaded globally via `styles.scss`.

| Prop | Values | Default |
|---|---|---|
| `zxGridDesktopSpan` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `1` |
| `zxGridTabletSpan` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `zxGridDesktopSpan` |
| `zxGridMobileSpan` | `1` \| `2` \| `3` \| `4` \| `5` \| `6` | `zxGridDesktopSpan` |

## Example: asymmetric two-column layout

```html
<zx-grid desktopColumns="5" mobileColumns="1" gap="xl" align="start">
  <zx-author-ratings zxGridItem zxGridDesktopSpan="2" [elementId]="id"></zx-author-ratings>
  <zx-author-comments zxGridItem zxGridDesktopSpan="3" [elementId]="id"></zx-author-comments>
</zx-grid>
```

Desktop: 40 % / 60 % (2 of 5 columns vs 3 of 5 columns).
Mobile: single column (stacked).
