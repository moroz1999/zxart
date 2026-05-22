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
<p appText="body">Content</p>
<p appText="bodySm">Secondary content</p>
<span appText="label" tone="muted">Metadata</span>
<label appLabel>Field label</label>
```

Use `app-text` only when creating a standalone typography wrapper:

```html
<app-text variant="body" tone="muted">Generated content</app-text>
```

## Rules

- Typography styling and HTML semantics are separate responsibilities.
- Existing semantic elements must use directives.
- `app-text` must not emulate semantic tags.
- Use `bodySm` instead of `caption` when the content is still normal prose but should be visually smaller than primary body text.
- `caption` is reserved for text that explains another object, such as figure captions and field hints.
- Allowed inputs are `variant`, `tone`, and `truncate`.
- Direct typography properties in component SCSS are forbidden: `color`, `text-decoration`, `font-weight`, `font-size`, `line-height`, `letter-spacing`, `text-transform`, `text-align`, `text-indent`.
- Legacy `zx*` typography directives are compatibility-only and must not be used in new templates.
