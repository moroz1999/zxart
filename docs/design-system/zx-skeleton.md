# zx-skeleton

Loading placeholder for lists and content. Use for initial load states — never show empty containers or spinners instead.

`ng-zxart/src/app/shared/ui/zx-skeleton/`

## Structural mimicry rule

A skeleton MUST mirror the structure of the block it replaces as closely as possible: same number of elements, same hierarchy, same shapes and proportions, same gaps and paddings. A flat strip of generic ribs is not acceptable when the real content has groups, captions, icons, images, or rows of fixed proportions.

If no existing variant fits the target layout, add a new tailored variant rather than reusing a generic one. The goal is that swapping the skeleton for the loaded content causes no visible reflow — same boxes in the same positions, only filled in.

## Props

| Prop | Type | Default |
|---|---|---|
| `variant` | `comment` \| `card` \| `row` \| `text` \| `prod-grid` \| `picture-grid` \| `tune-table` \| `search-groups` | `card` |
| `count` | number | `5` |
| `animated` | boolean | `true` |
| `lineHeight` | string | `16px` (only `text`) |

## Variants

| Variant | Use when |
|---|---|
| `comment` | Comment lists (image + header + text lines) |
| `card` | Card grids (image + title + text) |
| `row` | Simple list rows |
| `text` | Text blocks |
| `prod-grid` | Prod card grids (256px columns) |
| `picture-grid` | Picture card grids (320px columns) |
| `tune-table` | Tune table rows (main title + small/medium columns) |
| `search-groups` | Quick search dialog: each group has a small caption rib + 5 result item ribs (icon + title). `count` = number of groups. |
