# zx-panel

Universal layout container.

`ng-zxart/src/app/shared/ui/zx-panel/`

## Props

| Prop | Values | Default |
|---|---|---|
| `variant` | `elevated` \| `flat` | `elevated` |
| `radius` | `sm` \| `md` \| `lg` \| `xl` | — |
| `padding` | `none` \| `sm` \| `md` \| `lg` | — |
| `title` | string | — |
| `titleLevel` | `h2` \| `h3` | — |
| `contentBleed` | boolean | `false` |

## Rules

**Variant**: Use `elevated` for top-level standalone panels. Use `flat` for panels nested inside another panel.

**Content bleed**: Use `[contentBleed]="true"` when child content (e.g., tables) needs to go edge-to-edge while the title retains consistent padding. Do NOT use `padding="none"` as a workaround for tables.

**`padding="none"`**: Reserved for cases where the entire panel genuinely needs zero padding (e.g., image cards where everything is edge-to-edge and there is no title).
