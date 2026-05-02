# Error Handling

## Never swallow exceptions silently

`catch (\Throwable) {}` with an empty body is forbidden. If you catch an exception, always log it.

**Wrong:**
```php
try {
    $this->doSomething();
} catch (\Throwable) {
    // silent -- hides bugs, makes debugging impossible
}
```

**Correct:**
```php
use Monolog\Logger;

try {
    $this->doSomething();
} catch (\Throwable $e) {
    $this->logger->error('ClassName::methodName: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
}
```

This applies even when the operation is non-critical and the catch block falls back to a safe default -- the fallback is fine, but the error must still be logged.

## Logging utilities

- **In `controllerApplication` subclasses:** inject `Monolog\Logger` via constructor DI and use it for error logging.
- **`ErrorLog::getInstance()` is deprecated.** Keep it only in untouched legacy code that cannot yet be moved to DI.
- **In classes that extend `errorLogger`** (e.g. `LanguagesManager`, structure elements, actions): use the inherited `$this->logError($message)` -- it automatically uses `getErrorLogLocation()` as the location.

## Where to find runtime logs

All runtime logs land in `temporary/logs/` at the project root. When debugging a 500 / unexpected response, that's the first place to look.

- `temporary/logs/<YYYY-MM-DD>.log` — daily Monolog output. Both controller-level errors logged through DI `Monolog\Logger` (e.g. `ProdDetails::execute: ...` lines from `LoggedControllerApplication::assignError()`) and `ErrorLog::getInstance()` writes (legacy code) end up here. Each entry includes `IP`, `Referer`, and the failing `URL`, which is enough to find the offending request quickly.
- `temporary/logs/db_<timestamp>.log` — per-request SQL trace, written by `DbLoggableApplication`. One file per request whose application opted in. Useful when you need to see the actual queries a 500 ran before crashing.
- Inside Docker the same directory mounts at `/var/www/html/temporary/logs/`. Trace lines printed by the container reference that path; the host-side equivalent is `temporary/logs/`.

For each new error after a fix attempt, re-read the latest few entries of the day's file (`tail` the last ~80 lines) — Monolog appends, the most recent error is at the bottom.

## See also
- [REST API error responses](rest-api.md) -- how to return errors from controllers
