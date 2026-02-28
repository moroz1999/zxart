# Repositories and Database

## Repository Pattern
- All database queries (`Connection`, Query Builder) MUST live in repository classes, NEVER in services.
- Services orchestrate business logic; repositories handle data access.
- Repository location: `project/core/ZxArt/{Domain}/Repositories/`.
- Repositories are `readonly final class` with `Connection` injected via constructor.
- Repository methods return primitive types (IDs, counts, arrays), not domain objects or DTOs.
- Services use repository results (e.g. IDs) and load domain objects via `structureManager`.

## Database Table Names
- `Illuminate\Database\Connection` automatically adds `engine_` prefix to table names.
- In repositories, use table names WITHOUT the `engine_` prefix.
- Example: for table `engine_preferences`, use `private const TABLE = 'preferences';`

## No Raw SQL in Repositories
- NEVER use `$this->db->raw()` or string-concatenated SQL in repositories.
- Raw SQL bypasses Illuminate's table prefix handling and is a security risk (SQL injection).
- Always use the Query Builder methods (`->table()`, `->whereIn()`, `->orderBy()`, etc.).
- If a query requires a subquery with `LIMIT` (e.g., "top N by votes"), split into two queries:
  1. First query: get the IDs with the query builder.
  2. Second query: use `->whereIn('id', $ids)` to filter.
- Example:
```php
// BAD — raw SQL, bypasses prefix, injection risk:
$this->db->table($this->db->raw('(SELECT id FROM ' . self::TABLE . ' ORDER BY votes DESC LIMIT ' . $topN . ') AS top'))

// GOOD — two safe queries:
$topIds = $this->getSelectSql()->orderBy('votes', 'desc')->limit($topN)->pluck('id');
$this->getSelectSql()->whereIn('id', $topIds)->inRandomOrder()->limit($limit)->pluck('id');
```

## Database Query Results

The `Connection` is configured with `PDO::FETCH_ASSOC` (set in `trickster-cms/cms/core/di-definitions.php` via `$capsule->setFetchMode(PDO::FETCH_ASSOC)`). This means:

- `get()` and `first()` return plain PHP **arrays**, not objects or Eloquent models.
- Use array access syntax: `$row['column_name']`, NOT `$row->column_name`.
- `pluck()` also returns a **plain PHP array**, NOT an Illuminate `Collection`. Do NOT chain `->all()` after `pluck()`.
- Example:
```php
$rows = $this->db->table(self::TABLE)->get();
foreach ($rows as $row) {
    $id = (int)$row['id'];
    $name = $row['name'];
}

// pluck returns plain array directly:
$ids = $this->db->table(self::TABLE)->pluck('id'); // int[]
```
