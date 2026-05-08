# Colors: Design vs Project Sync

Source: claude.ai/design bundle `5K0sFKR1vbDrEZdXqwMq-A`, file `colors_and_type.css`.

## Result: base values are identical

All hex values in the four base ramps match exactly. The only difference is naming convention:
the design uses short names (`--neutral-50`), the project uses suffixed names (`--neutral-light-50`).
This difference lives only inside `_light.theme.scss` / `_dark.theme.scss` and is invisible to components.

## Semantic ramps — identical

Both project and design map the same way:

| Ramp | Maps to |
|---|---|
| `--primary-*` | blue |
| `--secondary-*` | neutral gray |
| `--danger-*` | red |
| `--warning-*` | yellow |

## Utility semantic variables — identical

| Variable | Value | Status |
|---|---|---|
| `--text-color` | `var(--secondary-800)` | Match |
| `--text-light-color` | `var(--secondary-600)` | Match |
| `--error-color` | `var(--danger-400)` | Match |
| `--background-dark` | `var(--secondary-200)` | Match |
| `--background-deep` | `var(--secondary-100)` | Match |
| `--date-color` | `var(--secondary-400)` | Match |
| `--pseudo-link-color` | `var(--warning-800)` | Match |
| `--icon-yellow` | `var(--warning-400)` | Match |

## Missing semantic variables (design uses, project lacks)

These must be added to `_colors.theme.scss` with explicit user approval before use.

| Variable | Light value | Dark value | Used for |
|---|---|---|---|
| `--surface` | `var(--white)` | `var(--secondary-100)` | Panel / card backgrounds (replaces hardcoded `--white`) |
| `--background-page` | `var(--secondary-50)` | `var(--secondary-50)` | Full-page background |

## Legacy variables used in design templates

The design prototype references `--link-color` and `--link-alt-color`. Both are defined in
`_legacy.theme.scss` only. They must NOT be used in new Angular components per `styles.md`.
Use `--primary-600` and `--warning-400` directly via component-level variables instead.
