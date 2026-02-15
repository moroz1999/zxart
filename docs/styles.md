# Styles Guide (STRICT)

This document defines MANDATORY styling rules.
Any deviation is considered a BLOCKER.

======================================================================

GENERAL

- New styles MUST be written in SCSS.
- After ANY changes to SCSS or theme files, the Angular project MUST be rebuilt using:
  composer run build
- No vendor prefixes (-webkit-, -moz-, -ms-, etc.).
- All comments in CSS/SCSS MUST be written in English only.

======================================================================

VARIABLE SYSTEM (MANDATORY)

VARIABLE HIERARCHY (TOP → BOTTOM)

1. Component variables
   --zx-<component>-*
2. Semantic theme variables
   --primary-*, --secondary-*, --surface-*, etc.
3. Base system variables
   --space-*, --radius-*, --font-*, animation variables

RULES

- Components MUST use ONLY component-level variables.
- Semantic and base variables MAY be used ONLY inside component theme files.
- Direct usage of semantic or base variables inside component SCSS is FORBIDDEN.
- Component-specific variables MUST NOT be used outside of their owning component.

======================================================================

COLORS AND SIZES

- NO hardcoded hex, rgb, hsl or raw spacing values anywhere.
- Raw color values are allowed ONLY as variable definitions in:
  _dark.theme.scss
  _light.theme.scss
- Adding or modifying colors in these files requires explicit user approval.
- SCSS variables are FORBIDDEN. Use CSS var() only.
- Reuse of variables from _legacy.theme.scss or any _legacy* file is PROHIBITED.
- Legacy link variables (--link-color, --link-alt-color) MUST NOT be used in new code.
- If a needed value is missing, use the closest existing one.

======================================================================

BASE THEME FILES

- _base.theme.scss contains base system variables only.
- Adding or modifying variables in _base.theme.scss is allowed ONLY with direct user permission.
- Theme files MUST contain ONLY CSS variable declarations.
- CSS rules (selectors + properties) are FORBIDDEN in theme files.

======================================================================

COMPONENT VARIABLES

- Each component MUST have its own theme file:
  _zx-<component>.theme.scss
- Component variables MUST:
  - Have exactly one responsibility
  - Use semantic or base variables internally
  - Follow naming:
    --zx-<component>-<semantic-purpose>
- Invalid names include:
  --zx-button-color-1
  --zx-card-custom
- Every component variable MUST be documented with an English comment.

======================================================================

TYPOGRAPHY (STRICT)

ALLOWED SEMANTIC STYLES

- heading-1
- heading-2
- heading-3
- body
- body-strong
- caption
- link
- link-alt

USAGE RULES

- Typography MUST be applied ONLY via directives:
  zxHeading1, zxHeading2, zxHeading3
  zxBody, zxBodyStrong, zxCaption
  zxLink, zxLinkAlt
- Direct usage of --font-* variables in components is FORBIDDEN.
- Custom typography variants or display styles are FORBIDDEN.
- Typography directives MUST be applied to the host element only.
- Combining typography directives with manual font overrides is FORBIDDEN.

FORBIDDEN EXAMPLES

- zxBody with custom font-size
- zxCaption with custom line-height

If a required directive is missing, ASK the user.

======================================================================

FONT SIZES AND SPACING

- Font-size tiers are defined in _typography.theme.scss:
  --zx-font-size-xs
  --zx-font-size-sm
  --zx-font-size-md
  --zx-font-size-lg
  --zx-font-size-xl
  --zx-font-size-xxl
- Font sizes MUST:
  - Use these variables only
  - Be rounded to whole pixels
- NO em or rem anywhere.
- Spacing variables (--space-*) MUST be multiples of 4px.

Components that need a specific size without semantic meaning MUST define a component-level variable, for example:
--zx-playlist-item-font-size: var(--zx-font-size-sm)

======================================================================

LAYOUT RULES

- For related elements, layout systems are MANDATORY:
  Flexbox, Grid, zx-stack
- Individual margins between related elements are FORBIDDEN.
- Negative margins are PROHIBITED.
- calc(-1 * ...) tricks are FORBIDDEN.
- For edge-to-edge content inside zx-panel, use:
  [contentBleed]="true"
- Use padding="none" ONLY when the entire panel genuinely needs zero padding.

======================================================================

TEMPLATES AND STRUCTURE

- Wrapper div elements used only for styling are FORBIDDEN.
- Use :host with @HostBinding('class').
- Wrappers are allowed ONLY when additional structural markup is required beyond ng-content.

======================================================================

CROSS-COMPONENT ISOLATION

- A component MUST NEVER override another component’s CSS variables.
- Parent components MUST NOT style child components via selectors.
- Customization MUST be exposed ONLY via:
  - Component inputs (size, variant, etc.)
  - Component-level CSS variables
- If a required size or variant is missing, it MUST be added to the child component properly.

======================================================================

SCSS RESTRICTIONS

- @extend is FORBIDDEN.
- Nesting deeper than 3 levels is FORBIDDEN.
- Parent selector abuse is FORBIDDEN.
- Mixins are allowed ONLY for structural patterns, never for styling values.

======================================================================

TRANSITIONS

- State changes MUST use short, subtle transitions.
- Allowed durations:
  --animation-xs (100ms)
  --animation-sm (200ms)
- Allowed properties:
  background-color, color, opacity, border-color
- Transitions on layout-triggering properties are FORBIDDEN.

======================================================================

LEGACY CODE

Legacy is defined as:
- Any file with _legacy prefix
- Any LESS-based styles
- Any variables outside the current theme system

Legacy code is READ-ONLY unless explicitly stated otherwise.

All legacy hardcoded values MUST be replaced with CSS variables following the same rules as SCSS.

======================================================================

CLEANUP RULES

- If a property or variable:
  - Is unused
  - Is used only once
  - Exists only to disable something with 0, transparent, etc.

  → It MUST be REMOVED entirely.

Temporary styles, TODO-styles, or “we’ll refactor later” solutions are PROHIBITED.

======================================================================

DECISION RULES

- If multiple valid solutions exist, the SIMPLEST one MUST be chosen.
- Overengineering is a violation.
- Visual correctness does NOT override architectural rules.
- “It works” is NOT a justification.

======================================================================

ENFORCEMENT

- Any violation of this guide is a BLOCKER.
- These rules are mandatory.
- No exceptions without explicit user approval.
