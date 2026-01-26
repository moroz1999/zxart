# Styles

## New code should be written in SCSS.

**CRITICAL: After any changes to SCSS or theme files, you MUST rebuild the Angular project using `composer run build` in the root directory.**

- **Comments**: All comments in CSS/SCSS files must be written in English only.
- **Styles and Variables**:
    - NO hardcoded hex/rgb or raw spacing.
    - NO SCSS variables (deprecated). Use CSS `var()`.
- **Architecture**:
    - **Base variables** (`--space-*`, `--font-*`, base colors, `--radius-*`, etc.) define the design system's foundation. They are stored in `src/app/shared/theme/_base.theme.scss`.
    - **Component variables** (e.g., `--button-bg`, `--input-color`) must be defined in separate files (one per component, e.g., `_button.theme.scss`). These variables can use base variables.
    - **Usage**: Components (Angular or Legacy) MUST use component variables ONLY. Direct use of base variables in components is FORBIDDEN.
    - **Enforcement**: This is a mandatory rule. Always check if you are using base variables directly and replace them with component variables.
    - If a component-level variable is missing, create it in a component-specific theme file.
    - NO `em` or `rem` in components and theme. Use font-size variables only.
    - Font sizes must be rounded to whole pixels.
    - Spacing (`--space-*`) must be multiples of 4px.
    - If a specific color/size is missing in theme, use the closest existing one.
    - All theme management is centralized in component-specific theme files, which are imported into the main theme.

## Legacy LESS
- Replace all hardcoded legacy property values with CSS variables. Add missing variables according to same rules as for SCSS. 