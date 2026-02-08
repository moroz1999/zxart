# Controllers and StructureManager

## getElementById() loading modes
`structureManager::getElementById($id, $parentId = null, bool $directlyToParent = false)`

- **`directlyToParent = false`** (default) — resolves the element through its URL path in the site hierarchy. For **public content** (pictures, tunes, prods, releases, parties) always use the default: `getElementById($id)`.
- **`directlyToParent = true`** — loads the element directly by ID, bypassing path resolution. **Required ONLY for user elements** and elements not rooted under the current language's URL tree: `getElementById($userId, null, true)`. Without `true`, user elements silently return `null`.

## Controller initialization
When initializing `structureManager` in a controller application, always provide `rootUrl` and `rootMarker` parameters to ensure correct URL generation and multilinguality support.
Example:
```php
$structureManager = $this->getService(
    'structureManager',
    [
        'rootUrl' => $controller->rootURL,
        'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
    ],
    true
);
$languagesManager = $this->getService('LanguagesManager');
$structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
```

## Controller URL name
Add `getUrlName()` in controller applications to avoid controller name prefix in entity URLs. Without it, all entities will get the controller name at the beginning of their URL.
Example:
```php
public function getUrlName()
{
    return '';
}
```

## Controller services
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

## AJAX Operations
- For AJAX operations in controllers, use the `action` parameter to distinguish between different types of requests (e.g., `add`, `update`, `delete`).
- Use the `json` renderer for AJAX responses.
- Ensure proper privilege checks are performed before executing any data modification operations.

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
