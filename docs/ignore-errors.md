# Backend IDE Warnings to Ignore

This file documents known IDE warnings that are safe to ignore because they are false positives
caused by missing or incorrect type stubs in third-party libraries.

## Illuminate Query Builder (illuminate/database v5.2)

### "Expected parameter of type 'string', '\Closure' provided" on `join()`
The `join()` method in Illuminate 5.2's Query Builder accepts a Closure as the first argument
(for building complex ON clauses), but the type hint only declares `string`. The Closure form
is fully supported at runtime; the warning is a false positive.

**Files affected:** any repository that uses `->join($table, function($join) { ... })`.

## structureElement / legacy CMS classes

### "Multiple definitions exist for class 'authorElement'" (and similar)
The IDE finds both the real class file and a test double in `tests/Doubles/Elements/`. Safe to ignore.

### Note on "Property accessed via magic method"
These should be **fixed** by adding `@property` PHPDoc annotations to the relevant class,
not ignored. Add to the specific element class or to `structureElement` base for universal properties.
Common properties declared on the base: `id`, `title`, `structureType`, `structureRole`, `structureName`, `dateCreated`, `dateModified`, `marker`.
