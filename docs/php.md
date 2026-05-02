# PHP

## Core Principles
- Stick to Domain Driven Design and Onion Architecture for all NEWLY written code.
- Use rules of SOLID principles.
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS use imports (`use`) for classes and namespaces. Fully qualified names in the code are prohibited.
- NEVER add unnecessary duplicate type casting.
- Use typed constants (e.g., `public const int MY_CONSTANT = 1;`).
- Place constants and variables at the beginning of the class, before any methods.
- The project is available at http://zxart.loc/

## Immutability and Type Safety
- DTOs must be 100% immutable. Use `readonly class` and constructor property promotion.
- Services and other stateless classes should be marked as `readonly class` if all their properties are immutable (e.g., dependencies injected via constructor). When a class is `readonly`, individual `readonly` modifiers on properties are redundant and should be omitted.
- Do NOT write PHPDoc `/** @var ... */` for `getService` calls if the class name is explicitly provided as the first argument (e.g. `getService(MyService::class)`). Modern IDEs and Psalm can infer the type from the class string.

## Coding Style
- Methods must follow SRP: each method does one thing. Extract private methods if a method handles multiple concerns or becomes hard to read at a glance.
- If nesting exceeds 2–3 levels, refactor: extract methods, use early returns, or split the logic.
- Avoid "comment ladders" (multiple sequential comments describing every line of code).
- Avoid inline method calls in conditions if they represent a state. Assign the result to a descriptive variable instead:
  ```php
  $isEditable = $element->isEditable();
  if ($isEditable) { ... }
  ```
  instead of `if ($element->isEditable()) { ... }`.
- Do NOT add a BOM header to any file.
- ALWAYS use strict comparisons (`===`, `!==`). Avoid "falsy" and "truthy" checks (e.g., use `if ($var === true)` instead of `if ($var)`).
- When receiving data from legacy CMS methods or properties that lack explicit return type hints (e.g. from `structureElement` properties or old CMS methods), explicitly cast them to the expected type (e.g., `(int)$element->id`, `(array)$manager->getData()`). If a method already has a native PHP type hint (e.g. `isEditable(): bool`), explicit casting is prohibited as redundant. Document these expectations via PHPDoc only if native type hints are missing.
- Do NOT use magic numbers. Use class constants for single values or Enums for sets of related values.
- Do NOT use `const array` for a closed set of allowed string/int values (e.g., allowed sort columns, directions, statuses). Use a backed `enum` instead — it provides type safety, exhaustiveness checks, and eliminates `in_array` validation boilerplate.

## Entity Types
- Use `EntityType` enum (`ZxArt\Shared\EntityType`) instead of hardcoded strings for entity type identifiers (e.g., `'author'`, `'prod'`, `'release'`, `'group'`).
- Pass `EntityType` directly to repository and service methods that accept it (`ImportIdOperator`, `AuthorshipRepository`, list repositories).
- For legacy CMS methods that accept string parameters (e.g., `$element->getAuthorsInfo()`, `$element->getShortAuthorship()`), use `EntityType::Prod->value` to extract the string value.

## Structure Types
- Use `StructureType` enum (`ZxArt\Shared\StructureType`) instead of hardcoded structure-type strings (e.g., `'zxProd'`, `'zxRelease'`, `'pressArticle'`).
- This is **distinct from `EntityType`**: `StructureType` matches CMS class names (`structureType` property), while `EntityType` covers authorship/ownership identifiers.
- Pass `StructureType::ZxProd->value` to legacy methods that accept structure type strings (e.g., `privilegesManager::checkPrivilegesForAction()`).
- Add a new case if you need a structure type not yet listed.

## Psalm
- NEVER use @psalm-suppress. Instead, add clear and minimal type annotations.
- Do NOT use `@var` by default. Allow it only when native PHP types cannot express the needed information, primarily for generics (including typed arrays) and complex Psalm array shapes.
- Do NOT add PHPDoc that only duplicates native parameter, property, or return types. Use PHPDoc only when it adds information that native types cannot express.
- Type declarations and PHPDoc unions must be the narrowest types justified by the real contract and actual call sites. Do NOT add extra union members "just in case".
- Before widening a type, verify both sides: the callee contract and the concrete values passed by callers. Generic library signatures must not be copied into local contracts if the local code only accepts a narrower subset.
- Do NOT hide missing types behind helper wrappers, broad casts, or normalization layers added only for static analysis. Prefer fixing the contract or the nearest reasonable source of the mixed value, as long as that can be done without disproportionate refactoring.
- Do NOT spread complex inline `@var` array-shape annotations through application code. If a long shape annotation is needed at the usage site, treat that as a signal to type the source data earlier.
- If a legacy method cannot be reasonably typed directly, introduce a named accessor with an explicit return type and keep the local cast or normalization inside that accessor, not at each call site.
- When handling legacy values, first convert them to the target type, then validate or branch on the typed result. Do NOT scatter repeated strict checks against multiple raw legacy representations such as `false`, `''`, `0`, and `'0'`.
- Annotate magic variables and methods in original legacy classes.

## Post-Task Checklist
- After finishing work on any PHP files, request IDE diagnostics (errors, warnings, notices) for all modified files via the MCP IDE tool and fix all reported issues in added code before considering the task done.

## Detailed Topics
For specific topics, see:
- [Services and Dependency Injection](php/services.md) - DI container, service design, dependency injection patterns
- [Repositories and Database](php/repositories.md) - Database table names, Query Builder, avoiding raw SQL
- [REST API and DTOs](php/rest-api.md) - REST API pattern, ObjectMapper usage, API verification and documentation
- [Error Handling](php/error-handling.md) - Never swallowing exceptions silently, logging utilities
