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
7. **Semantic Usage**: All components must be used semantically and for their intended purpose.
8. **Material UI**: The project uses Angular Material as the primary UI library. All new components must use Material components. PrimeNG is considered legacy and must be replaced during refactoring.
9. **Button Design**: Buttons must use Angular Material directives (`mat-button`, `mat-flat-button`, etc.). They are globally styled to match the design system using CSS variables defined in `_button.theme.scss`. Use the `color` attribute to switch between primary, accent, and warn states.
10. **UI Components Usage**: Only components from the `shared/ui` directory (design system) should be used for building user interfaces to ensure consistency across the application. Direct usage of Material components in features is discouraged if a design system equivalent exists.

### Reusable Subcomponents
1. **Legacy Components**: Legacy components and their variables must not be modified.
2. **Subcomponents**: To maintain consistency and reduce code duplication, repeating UI patterns must be extracted into standalone templates or components.
3. **Shared UI Components**: All design system components are located in `ng-zxart/src/app/shared/ui/`.

#### Available Components:
- `zx-button`: Versatile button component with multiple sizes (xs, sm, md) and colors (primary, secondary, danger).

