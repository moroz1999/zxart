# zx-pagination

Page navigation with loading state support. The page selector for every paginated list; hand-rolled prev/next controls are only for panels too narrow for numbered pages (the geo sidebar).

`ng-zxart/src/app/shared/ui/zx-pagination/`

## Props

| Prop | Type |
|---|---|
| `currentPage` | number |
| `pagesAmount` | number |
| `urlBase` | string |
| `visibleAmount` | number |
| `loading` | boolean — dims and disables the controls; the spinner belongs to [zxLoadingState](loading-states.md) on the list |

## Events

| Event | Payload |
|---|---|
| `pageChange` | number — new page number |
