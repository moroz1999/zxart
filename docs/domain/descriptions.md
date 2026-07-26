# Entity Description Rendering

Entity APIs HTML-entity-decode description content and return it without
presentation-only wrapper markup. Angular owns line-break presentation in the
concrete description blocks.

`DescriptionFormatter` resolves nested HTML entities and removes `pre` tags
while preserving their text content. Tune, picture, prod, and release detail
services use this formatter at the API boundary.

## Content contracts

- Tune and picture descriptions are plain text. Their detail-page blocks use
  `white-space: pre-wrap`.
- Prod descriptions are sanitized HTML when `htmlDescription` is true.
  Otherwise they are plain text rendered by the prod description block with
  `white-space: pre-wrap`.
- Release descriptions are sanitized HTML. The release detail block and release
  table row preserve source line breaks with `white-space: pre-wrap`.
- Legacy no-JavaScript tune, picture, and plain prod description blocks apply
  the same line-break presentation in their module styles.

## CMS fields

Tune, picture, and batch-upload descriptions use the `text` data chunk. Prod
and release descriptions use the `html` data chunk. Description fields do not
use server-side presentation data chunks.
