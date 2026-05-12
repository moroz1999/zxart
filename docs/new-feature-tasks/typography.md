Надо все использования типографики стандартизировать и завести typography компоненты. 
Любые прямые использования color, text-decoration, font-weight, font-size, line-height, letter-spacing, text-transform, text-align, text-indent - запрещены. Нужно использвать только готовые варианты типографики через компоненты и директивы.

Собери имеющиеся варианты по ВСЕМ ангуляр компонентам. Не сочиняй новых вариантов, жестко зафиксируй уже имеющиеся практики.
Когда будешь иметь полную картину - примени эту логику ко всем компонентам.

# Typography Architecture Rules (Angular)

## Core principle

Typography styling and HTML semantics are different responsibilities.

- Components control visual appearance.
- Directives decorate semantic HTML elements.

Never mix both responsibilities in one API.

---

# Architecture

```text
shared/ui/typography/
  text/
    text.component.ts
    text.component.scss

  directives/
    heading.directive.ts
    text.directive.ts
    label.directive.ts

  typography.types.ts
  typography.tokens.scss
```

---

# Decision Rules

## Use directive when semantic HTML already exists

Use directives for semantic elements:

- h1-h6
- p
- label
- span
- strong
- small

Example:

```html
<h1 appHeading="display">
<p appText="body">
<label appLabel>
```

Reason:

- preserves accessibility
- preserves document structure
- preserves SEO semantics

Directives only add typography styling.

---

## Use component when semantic wrapper does not exist yet

Use component only when the typography element itself is being created.

Example:

```html
<app-text variant="body">
  Content
</app-text>
```

Allowed cases:

- reusable UI widgets
- generated content
- CMS rendering
- isolated design-system blocks

Forbidden:

```html
<app-text as="h1">
```

Component must not dynamically emulate semantic tags.

---

# Strict Rule

If developer already has semantic HTML element:

```html
<h1>
<p>
<label>
<span>
```

then ALWAYS use directive.

If developer needs standalone typography wrapper:

then use component.

---

# API Rules

## Allowed inputs

```ts
variant
tone
truncate
```

---

## Forbidden inputs

```ts
fontSize
fontWeight
lineHeight
tag
as
element
customColor
```

Typography API must remain design-system driven.

---

# Typography Variants

```ts
type TypographyVariant =
  | 'display'
  | 'headline'
  | 'title'
  | 'body'
  | 'caption'
  | 'label';
```

Do not introduce uncontrolled variants.

---

# Styling Rules

Use centralized design tokens only.

Example:

```scss
--app-typography-body
--app-text-color-primary
```

No hardcoded values inside components/directives.

---

# Angular Requirements

Required:

- standalone APIs
- ChangeDetectionStrategy.OnPush
- signal-based inputs where appropriate

---

# Accessibility Rules

Do not replace semantic HTML with generic wrappers.

Bad:

```html
<app-text variant="headline">
```

Good:

```html
<h2 appHeading="headline">
```

---

# Complexity Rules

Avoid:

- polymorphic components
- runtime HTML tag switching
- dynamic render abstractions
- generic typography engines
- inheritance hierarchies

Prefer explicit architecture and predictable APIs.
```