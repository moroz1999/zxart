# Design System Components

All design system components are located in `ng-zxart/src/app/shared/ui/`.

## Available Components

### zx-button
Versatile button component with multiple sizes (xs, sm, md), colors, and square mode (removes horizontal padding, makes width equal to height).

**Colors and usage**:
- `primary` - Main call-to-action. Use for the primary action on a page (Submit, Save, Confirm).
- `secondary` - Secondary call-to-action. Use for important but not primary actions.
- `outlined` - Transparent background with gray border. Use for secondary actions like filters, Cancel buttons, or actions that shouldn't draw too much attention.
- `transparent` - No background or border. Use when many buttons appear in a row (pagination, letter navigation).
- `danger` - Destructive actions only (Delete, Remove, Revoke).

### zx-panel
Universal layout container with configurable variant (elevated, flat), border radius (sm, md, lg, xl) and padding (none, sm, md, lg). Optional `title` prop renders a standard heading inside the panel.

Props: `variant`, `radius`, `padding`, `title`, `titleLevel` (`h2` | `h3`), `contentBleed` (boolean, default `false`).

**Content bleed**: Use `[contentBleed]="true"` when child content (e.g., tables) needs to go edge-to-edge while the title retains consistent padding. The panel keeps its `padding` level for the title area, but the body area loses all padding. Do NOT use `padding="none"` as a workaround for tables — use `contentBleed` instead.

**padding="none"**: Reserved for cases where the entire panel genuinely needs zero padding (e.g., image cards where everything is edge-to-edge and there is no title).

**Variant rule**: Use `elevated` for standalone panels (top-level cards, sections). Use `flat` for panels nested inside another panel (inner grouping, embedded content).

### zx-stack
Flexbox-based layout container with configurable spacing (md, lg, xl) and direction (column, row).

### zx-user
Component for displaying user name with status icons (badges). Uses "icon name" layout.

### zx-skeleton
Loading placeholder component for lists and content. Variants:
- `comment` - For comment lists (image + header + text lines)
- `card` - For card grids (image + title + text)
- `row` - For simple list rows
- `text` - For text blocks

Props: `variant`, `count` (number of skeleton items), `animated` (shimmer effect, default true)

### zx-spinner
Compact loading spinner with size variants.

Props: `size` - `xs`, `sm`, `md`, `lg`, `xl` (default `md`)

### zx-table
Table wrapper inside an elevated panel with `contentBleed`. Props: `title` (optional heading above the table), `titleLevel`. The panel uses `contentBleed` so table rows go edge-to-edge while the title retains standard panel padding.

### zx-button-controls
Wrapper for groups of action buttons (dialogs, forms, toolbars). All button groups in the project MUST use this component instead of manual flex/gap layout.

Props: `align` - `start` (left-aligned), `end` (right-aligned, default), `distribute` (space-between)

Uses `gap: var(--gap-md)` between buttons

Example: `<zx-button-controls align="end"><zx-button color="outlined">Cancel</zx-button><zx-button color="primary">Save</zx-button></zx-button-controls>`

### zx-pagination
Page navigation component with loading state support.

Props: `currentPage`, `pagesAmount`, `urlBase`, `loading` (shows spinner overlay and disables controls), `visibleAmount`

Event: `pageChange` - emits new page number on click

### zx-filter-picker
Compact summary control with a popover list of checkboxes and search.

Props: `label`, `items` (`{id, label}`), `selectedIds`, `placeholder`, `searchEnabled`, `multi`

Event: `selectedIdsChange` - emits updated selection
