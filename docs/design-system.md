## Design System
The project follows a component-based approach for the design system. 

### Core Principles
1. **CSS Variables Only**: All colors must be specified exclusively through CSS variables.
2. **Fixed Palette**: Adding new colors or shades to the global palette is strictly prohibited. Use existing variables.
3. **Spacing System**: All distances (margins, paddings, gaps) must be multiples of 4 and set using variables: `--space-4`, `--space-8`, `--space-12`, `--space-16`, etc.
4. **Shadows**: There are two standard shadow variants:
    - `--shadow-thin`: For subtle elevations (e.g., cards, small buttons).
    - `--shadow-deep`: For prominent elements (e.g., modals, popups).
5. **No Hardcoded Palette in Components**: In component styles, it is forbidden to use base palette variables like `--gray8` directly. Instead, use semantic variables like `--text-color`, `--button-bg`, etc. If a needed semantic variable is missing, define it in the theme.

### Reusable Subcomponents
To maintain consistency and reduce code duplication, repeating UI patterns must be extracted into standalone Smarty templates (subcomponents).
- Never copy-paste logic for elements like controls, info blocks, or specific data displays across multiple components.

