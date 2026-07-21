# Skeletons

Angular loading placeholders must use concrete standalone skeleton components. Import only the skeleton that matches the current template.

Every skeleton component uses `ZxSkeletonVisibilityDirective` as a host directive. The directive sets `visibility: hidden` while the host is outside the viewport so off-screen skeleton animations do not consume rendering resources.

Do not add a facade skeleton component that imports multiple variants.

## Components

- `zx-card-skeleton`
- `zx-authors-table-skeleton`
- `zx-comment-skeleton`
- `zx-picture-card-skeleton`
- `zx-picture-details-skeleton`
- `zx-picture-grid-skeleton`
- `zx-parties-list-skeleton`
- `zx-prod-details-skeleton`
- `zx-prods-list-skeleton`
- `zx-prods-category-skeleton`
- `zx-release-details-skeleton`
- `zx-row-skeleton`
- `zx-screenshot-grid-skeleton`
- `zx-search-groups-skeleton`
- `zx-stats-section-skeleton`
- `zx-tags-cloud-skeleton`
- `zx-text-skeleton`
- `zx-tune-details-skeleton`
- `zx-tune-table-skeleton`
- `zx-skeleton-bone`

`zx-skeleton-bone` supports `inline=true` when a text placeholder must participate in the same line box as the content it represents.

## Structural Mimicry Rule

A skeleton must mirror the structure of the block it replaces as closely as possible: same number of elements, same hierarchy, same shapes and proportions, same gaps and paddings.

If no existing skeleton fits the target layout, add a new tailored skeleton instead of reusing a generic one.
