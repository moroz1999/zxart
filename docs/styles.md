# Styles

## New styles code should be written in SCSS.

**CRITICAL: After any changes to SCSS or theme files, you MUST rebuild the Angular project using `composer run build` from the project root directory.**

- **Comments**: All comments in CSS/SCSS files must be written in English only.
- **Styles and Variables**:
    - NO hardcoded hex/rgb or raw spacing.
    - NO SCSS variables (deprecated). Use CSS `var()`.
    - All colors and sizes must be formatted as CSS variables.
    - Adding or modifying variables in `_base.theme.scss` is allowed ONLY after direct user permission.
    - Reuse of variables from `_legacy.theme.scss` (or any other files with the `_legacy` prefix) is prohibited.
    - Legacy components and their variables must not be touched. CSS variables serve as a link between legacy and Angular.
- **Architecture**:
    - **Base variables** (`--space-*`, `--font-*`, `--radius-*`, etc.) define the design system's common foundation. They are stored in `src/app/shared/theme/_base.theme.scss`.
    - **Theme colors** define the color palette for different modes.
        - `_dark.theme.scss`: Default dark theme colors and semantic mappings.
        - `_light.theme.scss`: Light theme colors (activated by `.bright-mode` class) and inverted semantic mappings.
    - **Component variables** (e.g., `--zx-button-bg`, `--input-color`) must be defined in separate files (one per component, e.g., `_zx-button.theme.scss`). These variables should use semantic variables (`--primary-*`, `--secondary-*`, etc.) or base variables.
    - **Usage**: Components (Angular or Legacy) MUST use component variables ONLY. Direct use of base palette or base variables in components is FORBIDDEN.
    - **Enforcement**: This is a mandatory rule. Always check if you are using base variables directly and replace them with component variables.
    - If a component-level variable is missing, create it in a component-specific theme file.
    - If a property or variable is no longer needed or used only in one place and has a "default" value (transparent, 0, etc.) to hide it, REMOVE it entirely along with its declaration. Do not use default/transparent values to "disable" unused styles.
    - NO `em` or `rem` in components and theme. Use font-size variables only.
    - Font sizes must be rounded to whole pixels.
    - Spacing (`--space-*`) must be multiples of 4px.
    - If a specific color/size is missing in theme, use the closest existing one.
    - All theme management is centralized in component-specific theme files, which are imported into the main theme.

## Legacy LESS
- Replace all hardcoded legacy property values with CSS variables. Add missing variables according to same rules as for SCSS. 