# Colors

## Color System

- **Base Ramps**: `--blue-dark-*`, `--red-dark-*`, `--yellow-dark-*`, `--neutral-dark-*` (and their light equivalents).
- **Semantic Ramps**: `--primary-*`, `--secondary-*`, `--danger-*`, `--warning-*`.
- **Indices**: 50 to 950. In dark theme, 50 is the darkest and 950 is the lightest. In light theme, the mapping is inverted.
- **Fixed Palette**: Adding new colors or shades to the global palette is strictly prohibited. Use existing variables.

## Themes

The project supports Dark (`.dark-mode`) and Light (`.light-mode`) themes. Always use semantic variables to ensure correct theme switching.

## Rules

- All colors must be specified exclusively through CSS variables.
- In component styles, using base palette variables like `--blue-dark-500` directly is forbidden. Use semantic variables: `--text-color`, `--primary-500`, `--zx-button-bg`, etc.
- If a needed semantic variable is missing, define it in the component's theme file (e.g., `_zx-mycomponent.theme.scss`), not inline.
