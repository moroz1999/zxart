# Testing Guidelines

## Running Tests

```bash
composer run test        # Run all PHPUnit unit tests
composer run test:integration # Run the integration suite against the local database
composer run psalm       # Static analysis
cd ng-zxart && npm test  # Run all Angular Vitest tests once
cd ng-zxart && npm run test:watch # Run Angular tests in watch mode
```

Run one Angular spec from `ng-zxart/` with
`npm test -- src/app/path/example.spec.ts`, or filter by test name with
`npm test -- -t "test name"`.

## Angular Tests

- Angular tests use Vitest and live next to the tested source as `*.spec.ts`.
- Import test functions and mocks from `vitest`; do not use Jasmine APIs.
- Prefer isolated service and utility tests. Use Angular `TestBed` only when the
  behavior depends on Angular dependency injection or rendering.
- Keep each test deterministic and focused on observable behavior.

## Core Testing Principles

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

## Test Architecture

### Unit Tests vs Integration Tests

**Unit tests** — test isolated classes with mocked dependencies:
- New services in `project/core/ZxArt/`
- DTOs and value objects
- Pure functions and helpers

**Integration tests** — test with real infrastructure. They live in
`tests/Integration/`, which `phpunit.xml.dist` leaves out; they have their own
configuration, `phpunit-integration.xml.dist` (`composer test:integration`), and
run inside the `app` container, where the local database is reachable. Get
services from `controller::getInstance()->getContainer()`. They cover:
- Legacy CMS classes (`*Element`)
- Database queries
- File operations

`tests/Integration/bootstrap.php` makes a test that runs in its own process
read `tests/Integration/php/errors.ini`, which leaves deprecations out: the
Illuminate helpers raise them while the child process loads the autoloader,
and any output there fails the test.

### Entity writes

`tests/Integration/Entities/` writes each entity type through its
`<Entity>DataService` and through the legacy add and edit forms, and checks the
rows the database then holds (`EntityWriteTestCase`):

- each test runs in its own process and in a transaction that is rolled back;
- the rows are read back directly: the structure manager, the links manager
  and the collections keep what they loaded, so a reloaded element is the one
  written;
- a test signs in first (`signIn()`): the `crontab` user joins the given user
  groups and gets the given actions on the public root, then the public root is
  loaded as a request's path loads it — privileges are worked out per element
  as it loads, so nothing may be loaded before;
- the add form goes through the service its endpoint calls
  (`FormCreateService::submit()` for `/formdata/`, `AuthorAliasFormService`,
  `ReleaseBatchCreateService`), the edit form through `setElementFormData()`
  and `executeAction()` as `/ajax/` does; the json renderer makes them answer
  instead of redirect. The production batch upload needs uploaded files and
  the group alias add form has no entry point left, so neither is covered;
- joins are covered through the data services: what moves, what is deleted,
  and the refused privilege;
- reads are checked in the process that wrote: each `*WritesTest` reads back
  what a create and an update wrote through the entity's fields reader, and
  `EntityStatesTest` checks each state service against what the test set up;
  `EntitySearchTest` and `EntityMentionLookupTest` search and look up what the
  test wrote;
- what a test refers to — authors, groups, productions — it creates; only
  countries, cities, categories, a party and hardware codes are taken from the
  database.

### Import golden master

`tests/Integration/Import/` imports fixed DTOs through `ProdsService`,
`AuthorImportService` and `ImportGroupHandler` the way the import applications do (admin
structure manager, `crontab` user, no privilege checks) and compares everything
written with `snapshots/<test class>/<test>.json`:

- each test runs in its own process (`#[RunTestsInSeparateProcesses]` on the
  test class — the services keep import ids and elements in memory) and in a
  transaction that is rolled back;
- uploads go to a temporary folder; `FixtureProdsDownloader` serves downloads
  from `fixtures/files/` by the URL's file name, set in the container in place
  of `ProdsDownloader`;
- `ChangesSnapshot` reads the rows added since the test began and the files
  written, with new ids, the test's time and link positions normalised. It does
  not see changes to rows that existed before, so a scenario creates what its
  import updates;
- fixture titles and import ids are unlikely to match anything in the database,
  because the imports also look entities up by name.

A missing snapshot is recorded and the test reported incomplete; a stored one
is never rewritten — delete it to record it again, and only for a change of the
scenario, never of the import code.

`ProdImportAtomicityTest` and `AuthorImportAtomicityTest` stop an import
halfway — an author the structure cannot load, an import id store that throws
— and check that it left no element (`newElementIds()`).

## Writing Tests for Dbal Classes

### Mocking Query Builder

When mocking `Connection::table()`, return arrays (CMS uses `FETCH_ASSOC`):

```php
$builder = $this->createMock(Builder::class);
$builder->method('get')->willReturn(collect([
    ['id' => 1, 'name' => 'test'],
]));

$db = $this->createMock(Connection::class);
$db->method('table')->willReturn($builder);
```

### Mocking Builder Methods

When testing methods like `upsert()` or `updateOrInsert()`, match the real method signature:

```php
// ❌ WRONG: Mocking wrong method name
$builder = new class {
    public function upsert(...) { } // Code uses updateOrInsert!
};

// ✅ CORRECT: Mock the actual method
$builder = new class {
    public function updateOrInsert(array $attributes, array $values): bool {
        // capture and verify
        return true;
    }
};
```

## Testing Legacy CMS Classes

Legacy `*Element` classes (like `zxReleaseElement`, `zxProdElement`) are difficult to unit test because:

1. **Magic methods** — PHPUnit cannot mock `__get`, `__set`, `__call`
2. **Deep dependencies** — services, database, cache, translations
3. **CMS infrastructure** — property access goes through CMS data layer

### Don't Mock Magic Methods

```php
// ❌ This will fail in PHPUnit
$element->method('__get')->willReturnCallback(...);
// Error: Trying to configure method "__get" which cannot be configured
```

### Options for Testing Legacy Classes

**Option 1: Skip and document (recommended for complex classes)**
```php
public function testComplexBehavior(): void
{
    $this->markTestSkipped(
        'Requires CMS infrastructure. Use integration tests instead.'
    );
}
```

**Option 2: Extract logic to testable services**
```php
// Extract pure logic from legacy class to a service
class DescriptionFormatter
{
    public function cleanText(string $text): string { ... }
    public function limitText(string $text, int $limit): string { ... }
}

// Test the service instead
public function testCleanTextRemovesHtmlTags(): void
{
    $formatter = new DescriptionFormatter();
    $result = $formatter->cleanText('<p>Hello</p>');
    $this->assertSame('Hello', $result);
}
```

**Option 3: Test the logic pattern in isolation**
```php
// Test the algorithm without the class
public function testCleanTextBehavior(): void
{
    $text = "Test &amp; demo";
    $result = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $this->assertSame('Test & demo', $result);
}
```

## Test Naming and Assertions

### Name Tests to Match Behavior

```php
// ❌ Test name doesn't match actual behavior
public function testDtoLacksHardwareProdHasHardwareReturnsFalse(): void
{
    $this->assertTrue(...); // Confusing!
}

// ✅ Name matches the assertion
public function testDtoLacksHardwareProdHasHardwareReturnsTrue(): void
{
    // When DTO has no hardware info but prod does, return true (vtrdos compatibility)
    $this->assertTrue(...);
}
```

### Document Business Logic in Tests

```php
public function testDtoLacksHardwareProdHasHardwareReturnsTrue(): void
{
    // Business rule: When importing from vtrdos, hardware info is often missing.
    // If DTO lacks hardware but prod has it, consider compatible.
    $dto = $this->makeDtoWithReleases([[]]);
    $prod = $this->makeProdWithReleases([["zx48"]]);

    $this->assertTrue($this->service->areProdAndDtoCompatible($dto, $prod));
}
```

## Summary

| Scenario | Approach |
|----------|----------|
| New services with DI | Unit tests with mocks |
| Dbal classes | Mock Query Builder (returns arrays due to `FETCH_ASSOC`) |
| Legacy `*Element` classes | Integration tests or extract logic |
| Business logic | Document rules in test comments |
| Magic method classes | Don't mock magic methods; use alternatives |
