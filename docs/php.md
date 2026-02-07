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
- After API changes, verify by sending a request to `http://zxart.loc/` (e.g. `curl http://zxart.loc/firstpage/?action=newPictures&limit=5`). Use this for any API endpoints that don't require authorization.
- After changing the API, you MUST update the existing OpenAPI YAML file or add a new one in `api/api.yaml`.

## Controllers and StructureManager

### getElementById() loading modes
`structureManager::getElementById($id, $parentId = null, bool $directlyToParent = false)`

- **`directlyToParent = false`** (default) — resolves the element through its URL path in the site hierarchy. For **public content** (pictures, tunes, prods, releases, parties) always use the default: `getElementById($id)`.
- **`directlyToParent = true`** — loads the element directly by ID, bypassing path resolution. **Required ONLY for user elements** and elements not rooted under the current language's URL tree: `getElementById($userId, null, true)`. Without `true`, user elements silently return `null`.

### Controller initialization
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

## Database Table Names
- `Illuminate\Database\Connection` automatically adds `engine_` prefix to table names.
- In repositories, use table names WITHOUT the `engine_` prefix.
- Example: for table `engine_preferences`, use `private const TABLE = 'preferences';`

## No Raw SQL in Repositories
- NEVER use `$this->db->raw()` or string-concatenated SQL in repositories.
- Raw SQL bypasses Illuminate's table prefix handling and is a security risk (SQL injection).
- Always use the Query Builder methods (`->table()`, `->whereIn()`, `->orderBy()`, etc.).
- If a query requires a subquery with `LIMIT` (e.g., "top N by votes"), split into two queries:
  1. First query: get the IDs with the query builder.
  2. Second query: use `->whereIn('id', $ids)` to filter.
- Example:
```php
// BAD — raw SQL, bypasses prefix, injection risk:
$this->db->table($this->db->raw('(SELECT id FROM ' . self::TABLE . ' ORDER BY votes DESC LIMIT ' . $topN . ') AS top'))

// GOOD — two safe queries:
$topIds = $this->getSelectSql()->orderBy('votes', 'desc')->limit($topN)->pluck('id')->all();
$this->getSelectSql()->whereIn('id', $topIds)->inRandomOrder()->limit($limit)->pluck('id')->all();
```

## Database Query Results
- `Illuminate\Database\Query\Builder` returns **arrays**, not objects.
- Use array access syntax: `$row['column_name']`, NOT `$row->column_name`.
- `Builder::pluck()` returns a **plain array**, NOT a Collection. Do NOT chain `->all()` after `pluck()`.
- Example:
```php
$rows = $this->db->table(self::TABLE)->get();
foreach ($rows as $row) {
    $id = (int)$row['id'];
    $name = $row['name'];
}
```

## Error Logging in Controllers
- Use `ErrorLog::getInstance()->logMessage()` for logging errors in controllers.
- Logs are written to `{logs_path}/{date}.log`.
- First parameter is the location identifier (e.g., `'MyController::methodName'`).
- Second parameter is the error message (include stack trace for debugging).
- Example:
```php
use ErrorLog;

try {
    // ... code
} catch (Throwable $e) {
    ErrorLog::getInstance()->logMessage(
        'MyController::myMethod',
        $e->getMessage() . "\n" . $e->getTraceAsString()
    );
    // handle error response
}
```

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