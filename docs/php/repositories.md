# Repositories and Dbal

There is no ORM. Tables are read and written by hand, in the Dbal layer;
entities are supplied and stored by repositories. The two are different
things and never share a name.

## Repositories
- A service that supplies, saves, edits or deletes an entity and holds no
  business logic is a repository — whatever it is called today, it becomes
  `<Entity>Repository`.
- Location: `project/core/ZxArt/{Domain}/Repositories/<Entity>Repository.php`.
  A class named `*Repository` lives there and nowhere else.
- A repository hands out the entity (a legacy element) and stores it: `get()`,
  `create()`, `save()`, `touch()`, `delete()`. Rules about what a change means —
  validation, follow-up writes, privileges, logging — belong to writers and
  use cases ([entity-writes.md](entity-writes.md)), not to the repository.
- A repository may use Dbal classes; Dbal never uses a repository.

## Dbal
- All database queries (`Connection`, Query Builder) MUST live in Dbal classes, NEVER in services or repositories.
- Location: `project/core/ZxArt/{Domain}/Dbal/`, named `<Subject>Dbal` (`AuthorshipDbal`, `ProdSearchDbal`). No class in `Dbal/` is called a repository.
- Dbal classes are `readonly final class` with `Connection` injected via constructor.
- Dbal classes MUST extend `ZxArt\Shared\Dbal\AbstractDbal` for shared table-name helpers.
- Dbal methods return primitive types (IDs, counts, arrays) or plain row DTOs, not domain objects.
- Services use Dbal results (e.g. IDs) and load domain objects via `structureManager` or a repository.
- Filtering, sorting, counting, and pagination of database-backed lists MUST be performed by Dbal queries in the database, not by loading all rows into a service and slicing arrays in PHP.

## Checked by deptrac
`deptrac.php` keeps the layers apart: the Dbal layer may not use the
Repositories layer, and a `*Repository` outside `<Domain>/Repositories/` —
in `Dbal/` or anywhere else — falls into a layer nothing may use, so every use
of it is a violation. Run `composer deptrac`.

## Database Table Names
- `Illuminate\Database\Connection` automatically adds `engine_` prefix to table names.
- In Dbal classes, use `ZxArt\Shared\DatabaseTable` for shared table names instead of local string constants.
- `DatabaseTable` values are table names for Query Builder WITHOUT the `engine_` prefix, e.g. `module_author`, `module_zxmusic`, `structure_links`.
- Do NOT strip or add the `module_` prefix in Dbal classes. `module_` is part of the table name.
- Add the `engine_` database prefix manually only in rare places where Query Builder cannot apply it automatically.

## No Raw SQL in Dbal Classes
- NEVER use `$this->db->raw()` or string-concatenated SQL in Dbal classes.
- Raw SQL bypasses Illuminate's table prefix handling and is a security risk (SQL injection).
- Always use the Query Builder methods (`->table()`, `->whereIn()`, `->orderBy()`, etc.).
- If a query requires a subquery with `LIMIT` (e.g., "top N by votes"), split into two queries:
  1. First query: get the IDs with the query builder.
  2. Second query: use `->whereIn('id', $ids)` to filter.
- Example:
```php
// BAD — raw SQL, bypasses prefix, injection risk:
$this->db->table($this->db->raw('(SELECT id FROM engine_' . DatabaseTable::ZxMusic->value . ' ORDER BY votes DESC LIMIT ' . $topN . ') AS top'))

// GOOD — two safe queries:
$topIds = $this->getSelectSql()->orderBy('votes', 'desc')->limit($topN)->pluck('id');
$this->getSelectSql()->whereIn('id', $topIds)->inRandomOrder()->limit($limit)->pluck('id');
```

## Matching a Phrase
- A `like` on a user's phrase escapes the wildcards: `'%' . addcslashes($phrase, '\\%_') . '%'`. MariaDB's escape character is the backslash.
- SQLite has no default escape character, so the SQLite unit tests cannot show the escaping; an integration test on MariaDB does (`tests/Integration/Geo/PlaceSearchTest.php`).

## Database Query Results

The `Connection` is configured with `PDO::FETCH_ASSOC` (set in `trickster-cms/cms/core/di-definitions.php` via `$capsule->setFetchMode(PDO::FETCH_ASSOC)`). This means:

- `get()` and `first()` return plain PHP **arrays**, not objects or Eloquent models.
- Use array access syntax: `$row['column_name']`, NOT `$row->column_name`.
- `pluck()` also returns a **plain PHP array**, NOT an Illuminate `Collection`. Do NOT chain `->all()` after `pluck()`.
- Do NOT create helper wrappers only to satisfy Psalm for Query Builder result types.
- For `pluck()`, `min()`, `max()`, and similar query results, verify the real database column type first and add a narrow local `@var` annotation to the result variable.
- Example:
```php
$rows = $this->db->table(DatabaseTable::ZxMusic->value)->get();
foreach ($rows as $row) {
    $id = (int)$row['id'];
    $name = $row['name'];
}

// pluck returns plain array directly:
/** @var int[] $ids */
$ids = $this->db->table(DatabaseTable::ZxMusic->value)->pluck('id'); // int[]
```
