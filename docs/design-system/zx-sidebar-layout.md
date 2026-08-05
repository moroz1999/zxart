# zx-sidebar-layout

Two-column page body: a narrow companion column and the main content.

`ng-zxart/src/app/shared/ui/zx-sidebar-layout/`

The sidebar goes into the `[zxSidebar]` slot, the main content into the default
slot. On desktop the sidebar is the leading column and stays sticky below the
site header while the content scrolls. Below the `lg` breakpoint the grid
collapses to one column and both slots stack in DOM order — the sidebar first,
so its companion information stays above the content it belongs to.

The sidebar is meant for a condensed companion block; a slot that renders
nothing collapses and the content takes the whole width. The content column is
capped at a reading measure, so a wide viewport widens the margins rather than
the text.

```html
<zx-sidebar-layout>
  <zx-press-issue-nav zxSidebar [publication]="details.publication"></zx-press-issue-nav>

  <zx-stack spacing="xl">
    <!-- main content blocks -->
  </zx-stack>
</zx-sidebar-layout>
```

## Variables

| Variable | Purpose |
|---|---|
| `--zx-sidebar-layout-width` | Sidebar column width on desktop |
| `--zx-sidebar-layout-gap` | Gap between the columns |
| `--zx-sidebar-layout-content-max-width` | Reading measure the content column is capped at |
| `--zx-sidebar-layout-sticky-top` | Offset the sticky sidebar keeps below the header |
