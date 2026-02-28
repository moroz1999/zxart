# Error Handling

## Never swallow exceptions silently

`catch (\Throwable) {}` with an empty body is forbidden. If you catch an exception, always log it.

**Wrong:**
```php
try {
    $this->doSomething();
} catch (\Throwable) {
    // silent — hides bugs, makes debugging impossible
}
```

**Correct:**
```php
try {
    $this->doSomething();
} catch (\Throwable $e) {
    ErrorLog::getInstance()->logMessage('ClassName::methodName', $e->getMessage() . "\n" . $e->getTraceAsString());
}
```

This applies even when the operation is non-critical and the catch block falls back to a safe default — the fallback is fine, but the error must still be logged.

## Logging utilities

- **In `controllerApplication` subclasses and standalone code:** use `ErrorLog::getInstance()->logMessage($location, $message)`.
- **In classes that extend `errorLogger`** (e.g. `LanguagesManager`, structure elements, actions): use the inherited `$this->logError($message)` — it automatically uses `getErrorLogLocation()` as the location.

## See also
- [REST API error responses](rest-api.md) — how to return errors from controllers
