# Typography

Files:
- `ng-zxart/src/app/shared/ui/typography/text/text.component.ts`
- `ng-zxart/src/app/shared/ui/typography/directives/*.ts`
- `ng-zxart/src/app/shared/ui/typography/typography.tokens.scss`
- `ng-zxart/src/app/shared/ui/typography/typography.types.ts`

## Variants

| Variant | Use when |
|---|---|
| `display` | Page-level heading, usually `h1` |
| `headline` | Section heading, usually `h2` |
| `title` | Subsection or panel heading, usually `h3` |
| `body` | Main readable text, paragraphs, content |
| `bodySm` | Smaller readable paragraphs and long-form secondary content |
| `mono` | Text laid out on a fixed-width grid: magazine articles, scene-era descriptions. Carries the fixed-width face, and its own size and leading — a proportional rhythm leaves such copy crowded |
| `caption` | Text that describes or labels something else: image captions, form field hints |
| `label` | Compact labels, metadata, dates, counts, sidebar listings |

## Tones

| Tone | Use when |
|---|---|
| `primary` | Default text color |
| `muted` | Secondary text |
| `strong` | Emphasized body text |
| `link` | Primary clickable link text |
| `muted-link` | Secondary clickable link text |
| `link-alt` | Clickable link text on alternative backgrounds |
| `danger` | Error text |
| `inherit` | Text must inherit color from its host context |

## Usage

Use directives when the semantic element already exists:

```html
<h1 appHeading="display">Title</h1>
<p appText="body" tone="primary">Content</p>
<p appText="bodySm">Secondary content</p>
<span appText="label" tone="muted">Metadata</span>
<label appLabel>Field label</label>
```

Use `app-text` only when creating a standalone typography wrapper:

```html
<app-text variant="body" tone="primary">Generated content</app-text>
```

## Rules

- Native anchors use the global link color and decoration tokens, including the hover and focus decoration.
- Typography styling and HTML semantics are separate responsibilities.
- Existing semantic elements must use directives.
- `app-text` must not emulate semantic tags.
- Use `bodySm` instead of `caption` when the content is still normal prose but should be visually smaller than primary body text.
- `mono` is the only variant that sets a font family. Reach for it when the text's own line and column layout carries meaning, not to mark up code fragments inside prose.
- `caption` is reserved for text that explains another object, such as figure captions and field hints.
- Allowed inputs are `variant`, `tone`, and `truncate`.
- `truncate` sets `white-space: nowrap`, so every flex/grid item between the text and the element that defines the width must carry `min-width: 0`. Otherwise the automatic minimum size of those items is the min-content width of the unwrapped text: the track grows, nothing ellipsizes, and the layout overflows the viewport.
- Direct typography properties in component SCSS are forbidden: `color`, `text-decoration`, `font-weight`, `font-size`, `line-height`, `letter-spacing`, `text-transform`, `text-align`, `text-indent`.
- Legacy `zx*` typography directives are compatibility-only and must not be used in new templates.
