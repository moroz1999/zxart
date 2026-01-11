- Use DDD, SOLID, KISS, YAGNI.

PHP:
- Use strict mode.
- ALWAYS use full variable names.
- ALWAYS import namespaces.
- Project has 2 DI containers: use PHP-DI, don't use legacy custom. Use project/core/di-definitions.php for definitions.
- Don't add empty autowire into di. Autowiring is turned on by default.
- NEVER add unnesessary type casting.

Psalm:
- NEVER use @psalm-suppress. Instead, add clear and minimal type annotations.
- Annotate magic variables and methods in original legacy classes.

TEST:
- Bootstrap must be minimal: only autoload + environment setup.
- No class shadowing: never use `class_exists` to conditionally define doubles.
- Test doubles live in `tests/Doubles/` with proper namespaces.
- Doubles must match real public signatures (constructors + methods).
- Use stubs for return data, mocks for interaction, fakes for simple in-memory behavior.
- Construct all dependencies explicitly in the test (no hidden substitutions).
- Assert observable behavior, not internal implementation details.
- Use assertSame only when identity matters; otherwise compare values/fields.
- Use builders/fixtures for repeated test object setup.
- Tests must be deterministic: no global state, no load-order dependencies.
- One scenario per test, clear naming based on behavior and expected result.
- No error suppression; fail loud.
- Require manual review/approval for any production code changes triggered by tests.
- Run tests using "composer test"
- Run psalm using "composer psalm". Don't attempt to fix issues related to legacy classes, only fix the newly generated code.