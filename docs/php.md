## PHP:
- Stick to Domain Driven Design and Onion Architecture for all NEWLY written code.
- Use rules of SOLID principles.
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS use imports (`use`) for classes and namespaces. Fully qualified names in the code are prohibited.
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- NEVER add unnesessary duplicate type casting. 
- Use typed constants (e.g., `public const int MY_CONSTANT = 1;`).
- Place constants and variables at the beginning of the class, before any methods.
- Do NOT use `$element->getService()` from outside or inside of `structureElement`. Use PHP-DI to inject dependencies into services or other classes instead.
- The project is available at http://zxart.loc/
- DTOs must be 100% immutable. Use `readonly class` and constructor property promotion.
- Services and other stateless classes should be marked as `readonly class` if all their properties are immutable (e.g., dependencies injected via constructor). When a class is `readonly`, individual `readonly` modifiers on properties are redundant and should be omitted.
- Do NOT write PHPDoc `/** @var ... */` for `getService` calls if the class name is explicitly provided as the first argument (e.g. `getService(MyService::class)`). Modern IDEs and Psalm can infer the type from the class string.
- After API changes send the query to verify the results.
- After changing the API, you MUST update the existing OpenAPI YAML file or add a new one in `api/api.yaml`.

## Controllers and StructureManager
When initializing `structureManager` in a controller application, always provide `rootUrl` and `rootMarker` parameters to ensure correct URL generation and multilinguality support.
Example:
```php
$structureManager = $this->getService(
    'structureManager',
    [
        'rootUrl' => $controller->baseURL,
        'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
    ],
    true
);
$languagesManager = $this->getService('LanguagesManager');
$structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
```

## Services and Dependency Injection
- For ALL new code, services must be obtained through the DI container.
- All dependencies must be resolved automatically via PHP-DI through the constructor. Avoid using setters for dependency injection.
- Use `getService` method in controllers to obtain services.
- In `controllerApplication` based controllers, services should be obtained in the `initialize` method and stored as protected properties. This is considered legacy behavior to be followed until a major refactoring of the controller architecture.
- Example:
```php
class MyController extends controllerApplication
{
    protected MyService $myService;

    public function initialize(): void
    {
        $this->createRenderer();
        $this->myService = $this->getService(MyService::class);
    }
}
```
- Avoid using `new` for services that have dependencies or should be managed by the container.

## REST API and DTOs
- Controllers should be responsible for creating DTOs for REST responses.
- The workflow for REST API:
    1. Service retrieves entities from structre manager and/or data from repositories.
    2. Service returns its own internal DTOs to the controller.
    3. Controller maps service DTOs to REST DTOs using `Symfony\Component\ObjectMapper\ObjectMapper`.
- Mapping objects from service DTOs to REST DTOs (and vice-versa if needed) must be done exclusively through `ObjectMapper`.
- Example of `ObjectMapper` usage in controller:
```php
$internalDto = $this->myService->getData();
$restDto = $this->objectMapper->map($internalDto, MyRestDto::class);
// For arrays:
$restDtos = array_map(fn($dto) => $this->objectMapper->map($dto, MyRestDto::class), $internalDtos);
```
- Do NOT use `dataResponseConverters` for new code. All new REST endpoints must follow the DTO mapping scheme described above.
- Related entities (like comments) should be fetched via dedicated services rather than using direct entity methods (e.g., use `CommentsService->getCommentsList($elementId)` instead of `$entity->getCommentsList()`).

## CMS Core Mechanics
- **Recursive Deletion**: Method `structureElement::deleteElementData()` automatically deletes all child elements linked via `structure` links (or other links returned by `getDeletionLinkTypes()`). This ensures data integrity without manual recursion in services.

## AJAX Operations
- For AJAX operations in controllers, use the `action` parameter to distinguish between different types of requests (e.g., `add`, `update`, `delete`).
- Use the `json` renderer for AJAX responses.
- Ensure proper privilege checks are performed before executing any data modification operations.

## Structure Elements and Actions
- Keep structure elements (`structureElement`) lean by moving business logic into services.
- Use strict typing instead of `method_exists`. If multiple types share common behavior, introduce a shared interface.
- Check object types rather than method existence.
- Actions (`structureElementAction`) are equivalent to DDD use-cases. They are bound to specific entities and provide automatic privilege checks.
- The service container is available in actions via `$this->getService()`. New use-cases should be added as new actions when required.

## Coding Style
- Avoid "comment ladders" (multiple sequential comments describing every line of code).
- Avoid inline method calls in conditions if they represent a state. Assign the result to a descriptive variable instead:
  ```php
  $isEditable = $element->isEditable();
  if ($isEditable) { ... }
  ```
  instead of `if ($element->isEditable()) { ... }`.
- ALWAYS use strict comparisons (`===`, `!==`). Avoid "falsy" and "truthy" checks (e.g., use `if ($var === true)` instead of `if ($var)`).
- When receiving data from legacy CMS methods or properties that lack explicit return type hints (e.g. from `structureElement` properties or old CMS methods), explicitly cast them to the expected type (e.g., `(int)$element->id`, `(array)$manager->getData()`). If a method already has a native PHP type hint (e.g. `isEditable(): bool`), explicit casting is prohibited as redundant. Document these expectations via PHPDoc only if native type hints are missing.