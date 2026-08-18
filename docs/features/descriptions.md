# Entity descriptions — implementation

Domain rules: [../domain/descriptions.md](../domain/descriptions.md).

Entity APIs HTML-entity-decode description content and return it without
presentation-only wrapper markup. Angular owns line-break presentation in the
concrete description blocks.

`DescriptionFormatter` resolves nested HTML entities and removes `pre` tags
while preserving their text content. Tune, picture, prod, and release detail
services use this formatter at the API boundary.

The `pre` element belongs to the view. No data chunk, element, or service emits
it; the view decides to preserve the text's whitespace by rendering it through
`zx-preformatted` (`shared/ui`), which owns the fixed-width face, `pre-wrap`
whitespace, word breaking, and the horizontal scroll that keeps a long line
from widening the page.

## Content contracts

- Tune and picture descriptions are plain text. Their detail-page blocks use
  `white-space: pre-wrap`.
- Prod descriptions are sanitized HTML when `htmlDescription` is true, and the
  block renders them as ordinary flowing markup. Otherwise they are text laid
  out on a fixed-width grid and the block renders them through
  `zx-preformatted`.
- Release descriptions are sanitized HTML laid out on a fixed-width grid: the
  release detail block renders them through `zx-preformatted` in `html` mode.
  The release table row preserves source line breaks with `white-space: pre-wrap`,
  because a fixed-width block would widen the table's columns.
- Press article content is plain text on a fixed-width grid and renders through
  `zx-preformatted`.
- Legacy no-JavaScript tune, picture, and plain prod description blocks apply
  the same line-break presentation in their module styles.

## CMS fields

Tune, picture, and batch-upload descriptions use the `text` data chunk. Prod
and release descriptions use the `html` data chunk. Prod carries an
`htmlDescription` checkbox that selects between the two prod contracts above.
Description fields do not use server-side presentation data chunks.
