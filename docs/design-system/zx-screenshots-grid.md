# zxScreenshotsGrid

Screenshot thumbnail grid layout directive.

`ng-zxart/src/app/shared/directives/screenshots-grid.directive.ts`

## Contract

- Apply `zxScreenshotsGrid` to a container of screenshot cells or their placeholders.
- Five thumbnail columns on desktop, four below `lg`, two below `sm`.
- The first cell of a grid that opens with an enlarged screenshot carries
  `screenshots-grid-featured`, which spans two columns and two rows. It is
  exactly 512×384 px when the grid has enough room and shrinks proportionally
  on narrower layouts.
- Every cell keeps the thumbnail aspect ratio
  (`--zx-screenshots-grid-cell-ratio`), so a row's height does not depend on
  whether its images have arrived.
- `zx-screenshot-grid-skeleton` applies the directive as a host directive, so the
  placeholder occupies the same tracks as the real thumbnails at every
  breakpoint.
- Use this directive instead of feature-specific screenshot grid SCSS.
