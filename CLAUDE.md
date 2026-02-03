# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ZX-Art (zxart.ee) is an archive of ZX Spectrum-related art objects: graphics, music, software, authors, and groups. Built on Trickster CMS (legacy, undocumented) with an Angular frontend for modern components.

## Commands

```bash
# PHP tests
composer test

# Static analysis
composer psalm

# Angular build (required after any SCSS changes)
composer run build

# Angular dev server
cd ng-zxart && npm start
```

## Project Structure

- `/project/` - Main domain code (extends CMS structure)
  - `/project/core/ZxArt/` - Modern services with namespaces (all new code goes here)
  - `/project/core/` - Legacy services, models, helpers
  - `/project/modules/structureElements/{type}/` - Module code by element type
  - `/project/templates/public/` - Smarty templates (legacy view system)
  - `/project/css/public/` - Legacy CSS (auto-bundled, use `module.{name}.css` or `component.{name}.css`)
- `/ng-zxart/` - Angular 19 frontend
  - `/ng-zxart/src/app/features/` - Feature modules (FSD structure)
  - `/ng-zxart/src/app/shared/ui/` - Design system components
  - `/ng-zxart/src/app/shared/theme/` - CSS variables and themes
- `/api/` - OpenAPI specs (update after any API changes)
- `/trickster-cms/` - CMS source (dev environment)
- `/tests/` - PHPUnit tests
- `/temporary/` - Cache files (never access directly)

## Architecture

### CMS Structure Elements
Content is organized as a hierarchy of "Structure Elements". Each element has a type (e.g., `comment`, `zxProd`). Element code location: `{package}/modules/structureElements/{type}/`:
- `structure.class.php` - Main class
- `structure.actions.php` - Available actions
- `action.{actionName}.class.php` - Action implementation (class name: `{actionName}{ModuleName}`)

### Action System
Actions are DDD use-cases bound to entities with automatic privilege checks. URL pattern: `index.php?id={elementId}&action={actionName}`.

### Link Types
Elements link via `LinkTypes` enum. Never hardcode link type strings.

### Dependency Injection
Two containers exist: use PHP-DI (definitions in `project/core/di-definitions.php`), not the legacy custom container. In controllers, obtain services in `initialize()` method via `$this->getService(ServiceClass::class)`.

### REST API Pattern
1. Service retrieves entities/data
2. Service returns internal DTOs
3. Controller maps to REST DTOs via `Symfony\Component\ObjectMapper\ObjectMapper`

## PHP Rules

- Strict mode, DDD, SOLID, Onion Architecture for new code
- Use `readonly class` for immutable services and DTOs
- Constructor property promotion for DTOs
- Strict comparisons only (`===`, `!==`)
- Use typed constants with visibility
- Explicit type casting for legacy CMS methods lacking type hints
- No `@psalm-suppress` - add proper type annotations instead
- Assign method results to descriptive variables before conditionals

## Angular Rules

- Standalone components only (no modules except AppModule for bootstrapping)
- Material UI required (PrimeNG is legacy)
- Feature Sliced Design: `features/`, `entities/`, `shared/`
- Custom elements with `app-` prefix for integration with Smarty templates
- All text via `ngx-translate` (en.json, ru.json, es.json)
- Separate files for template, styles, logic

## Styles

- SCSS for new styles, CSS variables only (no SCSS variables, no hardcoded values)
- Typography via directives: `zxHeading1`, `zxHeading2`, `zxHeading3`, `zxBody`, `zxBodyStrong`, `zxCaption`, `zxLink`, `zxLinkAlt`
- Spacing: `--space-*` (multiples of 4px)
- Component-specific variables in `_zx-{component}.theme.scss`
- Use `zx-stack`, `zx-panel` for layout
- **Rebuild required after any SCSS change**: `composer run build`

## Documentation

See `/docs/` for detailed rules:
- `rules.md` - Common rules
- `domain.md` - Project domain and entities
- `architecture.md` - CMS and system concepts
- `php.md` - PHP coding standards
- `angular.md` - Angular integration
- `styles.md` - CSS/SCSS rules
- `design-system.md` - UI component guidelines
