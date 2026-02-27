# Shadows

Three base shadow tokens, theme-aware (overridden in `.dark-mode` and `.light-mode`):

| Token | Use when |
|---|---|
| `--shadow-sm` | Subtle elevation: popovers, tooltips, small dropdowns |
| `--shadow-md` | Medium elevation: panels, cards, floating elements |
| `--shadow-lg` | Deep elevation: modals, drag previews, sticky bars |

## Rules

- Components must define their own `--zx-<component>-shadow` variable referencing a base token.
- Hardcoded `box-shadow` values are forbidden.
