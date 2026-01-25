# Styles

## New code should be written in SCSS.

- **Comments**: All comments in CSS/SCSS files must be written in English only.
- **Styles and Variables**:
    - NO hardcoded hex/rgb or raw spacing.
    - NO SCSS variables (deprecated). Use CSS `var()`.
    - Base variables (colors, sizes, radius) must NOT be used in components directly (e.g., NO `--gray-4`, YES `--input-color`).
    - If a component-level variable is missing, add it to `_theme.scss`.
    - NO `em` or `rem` in components and theme. Use font-size variables from `_theme.scss` only.
    - If a specific color/size is missing in theme, use the closest existing one.
    - For `rgba`, use `-rgb` versions: `rgba(var(--shadow-deep-rgb), 0.5)`.
    - All theme management is centralized in `_theme.scss`.

## Legacy LESS
- Replace all hardcoded legacy property values with CSS variables. Add missing variables according to same rules as for SCSS. 