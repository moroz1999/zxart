# Typography

Files: `ng-zxart/src/app/shared/directives/typography/typography.directives.ts`, `ng-zxart/src/app/shared/theme/_typography.theme.scss`

## Directives

| Directive | Class | Use when |
|---|---|---|
| `zxHeading1` | `.zx-heading-1` | Page title |
| `zxHeading2` | `.zx-heading-2` | Section heading |
| `zxHeading3` | `.zx-heading-3` | Subsection or panel heading |
| `zxBody` | `.zx-body` | Main readable text, paragraphs, content |
| `zxBodyStrong` | `.zx-body-strong` | Emphasized text, names, labels that need to stand out |
| `zxBodySm` | `.zx-body-sm` | Secondary text in compact views — smaller, no semantic role |
| `zxBodySmMuted` | `.zx-body-sm-muted` | Secondary text that is smaller and visually dimmed — metadata, counts, dates, sidebar content |
| `zxCaption` | `.zx-caption` | Text that *describes or labels something else*: image captions, form field hints |
| `zxLink` | `.zx-link` | Clickable links (primary color) |
| `zxLinkAlt` | `.zx-link-alt` | Links on alternative backgrounds (warning/amber color) |

## Choosing between similar directives

**`zxBodySm` vs `zxBodySmMuted` vs `zxCaption`** — all three use `--font-sm`:

- `zxBodySm` — smaller size, no color override. For compact views where the text is just smaller.
- `zxBodySmMuted` — smaller size + muted color (`--text-light-color`). For visually secondary content: metadata, dates, counts, sidebar listings. No semantic role implied.
- `zxCaption` — smaller size + muted color + **semantic role**: this text describes or labels something specific (text under an image, hint under a form field). Use only when the text literally serves as a caption/description for another element.

## Rules

- Direct use of `--font-*` variables in components is prohibited. Use typography directives or `@include` mixins from `_typography.theme.scss`.
- Custom font sizes or weights outside this system are forbidden.
- SCSS mixins are available for component stylesheets: `@include heading-1`, `@include body`, `@include body-sm-muted`, etc.
