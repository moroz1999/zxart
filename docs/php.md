## PHP:
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS import namespaces.
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- NEVER add unnesessary duplicate type casting. 
- Use typed constants (e.g., `public const int MY_CONSTANT = 1;`).
- Place constants and variables at the beginning of the class, before any methods.

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