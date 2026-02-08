# Services and Dependency Injection

## Dependency Injection
- For ALL new code, services must be obtained through the DI container. Except the controllers.
- All dependencies must be resolved automatically via PHP-DI through the constructor. Avoid using setters for dependency injection.
- Avoid using `new` for services that have dependencies or should be managed by the container.

## DI Container Configuration
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- Do NOT use `$element->getService()` from outside or inside of `structureElement`. Use PHP-DI to inject dependencies into services or other classes instead.

## Service Design
- Services and other stateless classes should be marked as `readonly class` if all their properties are immutable (e.g., dependencies injected via constructor). When a class is `readonly`, individual `readonly` modifiers on properties are redundant and should be omitted.
- Keep structure elements (`structureElement`) lean by moving business logic into services.
- Related entities (like comments) should be fetched via dedicated services rather than using direct entity methods (e.g., use `CommentsService->getCommentsList($elementId)` instead of `$entity->getCommentsList()`).
