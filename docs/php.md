## PHP:
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS use imports (`use`) for classes and namespaces. Fully qualified names in the code are prohibited.
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- NEVER add unnesessary duplicate type casting. 
- Use typed constants (e.g., `public const int MY_CONSTANT = 1;`).
- Place constants and variables at the beginning of the class, before any methods.
- DTOs must be 100% immutable. Use `readonly class` and constructor property promotion.
- Do NOT write PHPDoc `/** @var ... */` for `getService` calls if the class name is explicitly provided as the first argument (e.g. `getService(MyService::class)`). Modern IDEs and Psalm can infer the type from the class string.

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