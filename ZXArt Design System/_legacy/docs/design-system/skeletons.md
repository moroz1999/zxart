# Skeletons

Angular loading placeholders must use concrete standalone skeleton components. Import only the skeleton that matches the current template.

Do not add a facade skeleton component that imports multiple variants.

## Components

- `zx-card-skeleton`
- `zx-comment-skeleton`
- `zx-picture-grid-skeleton`
- `zx-prod-details-skeleton`
- `zx-prods-list-skeleton`
- `zx-row-skeleton`
- `zx-screenshot-grid-skeleton`
- `zx-search-groups-skeleton`
- `zx-text-skeleton`
- `zx-tune-table-skeleton`
- `zx-skeleton-bone`

## Structural Mimicry Rule

A skeleton must mirror the structure of the block it replaces as closely as possible: same number of elements, same hierarchy, same shapes and proportions, same gaps and paddings.

If no existing skeleton fits the target layout, add a new tailored skeleton instead of reusing a generic one.
