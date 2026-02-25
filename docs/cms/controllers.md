# Controllers and StructureManager

## getElementById() loading modes
`structureManager::getElementById($id, $parentId = null, bool $directlyToParent = false)`

- **`directlyToParent = false`** (default) — resolves the element through its URL path in the site hierarchy. For **public content** (pictures, tunes, prods, releases, parties) always use the default: `getElementById($id)`.
- **`directlyToParent = true`** — loads the element directly by ID, bypassing path resolution. **Required ONLY for user elements** and elements not rooted under the current language's URL tree: `getElementById($userId, null, true)`. Without `true`, user elements silently return `null`.

## structureManager context

- **Public apps** — use the default SM: `$this->getService(structureManager::class)`.
- **Admin apps** — call `$this->getService('adminStructureManager')` directly.
- **Complex apps** that switch between admin/public based on a request parameter (e.g. `ajaxSearch`, `api`) — explicitly request the appropriate SM:
```php
if ($this->mode === 'admin') {
    $structureManager = $this->getService('adminStructureManager');
} else {
    $structureManager = $this->getService('publicStructureManager');
}
```

**Named SM keys:**
- `structureManager::class` / `'structureManager'` — public SM (default)
- `'publicStructureManager'` — explicit independent public SM factory
- `'adminStructureManager'` — admin SM factory (also overrides `structureManager::class` in the container as a side effect)

**Note:** `setService('structureManager', ...)` no longer exists. Do not use `->method('setService', ...)` in `di-definitions.php` for SM injection.

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
- Use `getService(MyService::class)` in controllers to obtain services — always use class constants, never string literals.
- Services with no special configuration are resolved automatically by PHP-DI autowiring; explicit `di-definitions.php` entries are only needed for services that require constructor/method parameters.
- Example:
```php
class MyController extends controllerApplication
{
    public function execute($controller): void
    {
        $myService = $this->getService(MyService::class);
    }
}
```

## AJAX Operations
- For AJAX operations in controllers, use the `action` parameter to distinguish between different types of requests (e.g., `add`, `update`, `delete`).
- Use the `json` renderer for AJAX responses.
- Ensure proper privilege checks are performed before executing any data modification operations.

## Error Logging in Controllers
- Use `ErrorLog::getInstance()->logMessage()` for logging errors in controllers.
- Log all exceptions thrown by services used in controllers.
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
