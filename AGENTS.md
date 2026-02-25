# AGENTS.md

This file contains the most CRITICAL rules that ALL agents must follow. For detailed information, see the documentation tree below.

## CRITICAL RULES

### General
- Keep it simple, don't invent unnecessary checks.
- Before each new task, read `AGENTS.md` and review the relevant rules if task scope differs from previous.
- All documentation and code comments MUST be in English, even if the user communicates in Russian or another language.
- After completing a task, re-read the task description and verify every point.
- After finishing the task code, it is necessary to read the rules on this topic from the .md docs once again and double-check your changes.
- Before starting a task, you MUST read the relevant documents from the DOCUMENTATION TREE below.
- Documentation updates must be placed in the appropriate .md file (e.g., PHP rules in php.md).
- Any new knowledge about functionality must be added to separate sub-documents within `domain.md`.
- Documentation additions in `docs` must be concise, clear, and only about the core points.
- ALWAYS add newly created files to GIT immediately after creation.
- When the IDE is in 'Ask' (readonly) mode, it is STRICTLY FORBIDDEN to do anything except answering the user's question. No file modifications or tool calls that change state are allowed.
- ALWAYS use MCP tools (JetBrains IDE) when available for code search, file reading, navigation, and locating files, methods, and classes instead of Grep/Glob/Read/Bash.
- Do not scan the whole project by file extension. Use targeted paths or direct file reads instead.
- Do not run naive recursive searches over the entire repository. Pick the specific directories from the documented project structure that match the task.
- Do not use `em` for icon sizes when fixed pixel size is possible. Use `px` via component/theme CSS variables.

## DOCUMENTATION TREE

Read ONLY the documents relevant to your task.

### Core Documentation
- **[docs/domain.md](docs/domain.md)** - Project domain and entities

### Backend (PHP)
- **[docs/cms.md](docs/cms.md)** - CMS structure, modules, actions, privileges, view system
- **[docs/php.md](docs/php.md)** - PHP coding standards, DDD, SOLID, immutability, type safety
- **[docs/testing.md](docs/testing.md)** - Testing guidelines, unit vs integration tests, mocking, test doubles

### Frontend (Angular)
- **[angular.md](docs/angular.md)** - Angular rules, FSD architecture, standalone components
- **[styles.md](docs/styles.md)** – SCSS rules, CSS variables, typography, themes, layout
- **[design-system.md](docs/design-system.md)** – Design system principles, colors, spacing, shadows, borders

### Legacy
- **[legacy-frontend.md](docs/legacy-frontend.md)** – Legacy Smarty templates, CSS naming conventions, buttons, layout

## COMMANDS

```bash
# PHP tests
composer test

# Static analysis
composer psalm

# Angular build (required after any changes to ng-zxart/ — see docs/angular.md)
docker compose run --rm node run build:docker
```

## PROJECT STRUCTURE

- `/project/` - Main domain code (extends CMS structure)
  - `/project/core/ZxArt/` – Modern services with namespaces (all new code goes here)
  - `/project/core/` - Legacy services, models, helpers
  - `/project/modules/structureElements/{type}/` - Module code by element type
  - `/project/templates/public/` - Smarty templates (legacy view system)
  - `/project/css/public/` - Legacy CSS (auto-bundled)
- `/ng-zxart/` - Angular 19 frontend
  - `/ng-zxart/src/app/features/` - Feature modules (FSD structure)
  - `/ng-zxart/src/app/shared/ui/` - Design system components
  - `/ng-zxart/src/app/shared/theme/` - CSS variables and themes
- `/api/` - OpenAPI specs (update after any API changes)
- `/tests/` - PHPUnit tests
- `/docs/` - All documentation
