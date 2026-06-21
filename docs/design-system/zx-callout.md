# zx-callout

Accent callout: a recessed inset box with a colored left rule. The visual atom behind the rating strip and the picture/tune provenance boxes.

`ng-zxart/src/app/shared/ui/zx-callout/`

## Props

| Prop | Values | Default |
|---|---|---|
| `accent` | `warning` \| `primary` \| `none` | `warning` |
| `surface` | `deep` \| `raised` | `deep` |
| `orientation` | `row` \| `column` | `column` |
| `align` | `start` \| `center` \| `stretch` | `stretch` |
| `wrap` | boolean | `true` |
| `bordered` | boolean | `false` |
| `interactive` | boolean | `false` |
| `label` | string | — |
| `href` | string | — |
| `ariaLabel` | string | — |

## Rules

**Surface**: `deep` is the recessed inset look (party/prod provenance, rating strip). `raised` sits on the card surface and is meant for bordered, interactive items.

**Label**: `label` renders a standardized uppercase caption header; its typography is fixed by the component. Pass an already-translated string. Content goes through `ng-content`.

**Link**: setting `href` turns the whole callout into a full-area link (it also enables the interactive hover). Provide `ariaLabel` when the projected content has no accessible text.
