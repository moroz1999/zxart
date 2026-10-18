# Entity descriptions — implementation

Domain rules: [../domain/descriptions.md](../domain/descriptions.md).

Entity APIs HTML-entity-decode description content and return it without
presentation-only wrapper markup. Angular owns line-break presentation in the
concrete description blocks.

`DescriptionFormatter` resolves nested HTML entities. Tune, picture, prod, and
release detail services use this formatter at the API boundary. It leaves the
markup alone: a `pre` block inside formatted text is part of the content.

The `pre` element belongs to the view. No data chunk, element, or service emits
it; the view decides to preserve the text's whitespace by rendering it through
`zx-preformatted` (`shared/ui`), which owns the fixed-width face, `pre-wrap`
whitespace, word breaking, and the horizontal scroll that keeps a long line
from widening the page.

## Content contracts

- Tune and picture descriptions are plain text. Their detail-page blocks use
  `white-space: pre-wrap`.
- Prod descriptions (`htmlDescription`) and instructions (`htmlInstructions`) are
  sanitized HTML when their flag is true,
  and their blocks render them as ordinary flowing markup. Otherwise they are
  text laid out on a fixed-width grid and the blocks render them through
  `zx-preformatted`. Both are decoded by `DescriptionFormatter`.
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
`htmlDescription` and an `htmlInstructions` checkbox, each selecting between
the two prod contracts above for its own text.
Description fields do not use server-side presentation data chunks.
