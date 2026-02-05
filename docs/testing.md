# Testing Guidelines

## Running Tests

```bash
composer test        # Run all PHPUnit tests
composer psalm       # Static analysis
```

## Test Architecture

### Unit Tests vs Integration Tests

**Unit tests** — test isolated classes with mocked dependencies:
- New services in `project/core/ZxArt/`
- DTOs and value objects
- Pure functions and helpers

**Integration tests** — test with real infrastructure:
- Legacy CMS classes (`*Element`)
- Database queries
- File operations

## Writing Tests for Repository Classes

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
| Repository classes | Mock Query Builder (returns arrays due to `FETCH_ASSOC`) |
| Legacy `*Element` classes | Integration tests or extract logic |
| Business logic | Document rules in test comments |
| Magic method classes | Don't mock magic methods; use alternatives |
