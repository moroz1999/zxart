# Converting the database to InnoDB — plan

Overview: [README.md](README.md). Why: [backend.md](backend.md#transactions).

Most content tables are MyISAM, which ignores transactions. Converting them lets
an approved plan run inside one transaction, and lets integration tests roll
back everything they wrote instead of cleaning up after themselves.

## What the local database shows

Checked on the local copy (production must be checked the same way first — the
local copy already has more InnoDB tables than the dump in `db/`):

- 70 MyISAM tables, about 322 MB data and indexes; the largest are
  `structure_links` (1.1 M rows, 109 MB), `structure_elements` (470 k, 50 MB),
  `import_origin`, `votes_history`, `search_log`.
- Every `AUTO_INCREMENT` column leads an index (InnoDB requires it): nothing to
  change.
- One `FULLTEXT` index, `module_tag.synonym`; no code runs `MATCH()` against it.
  InnoDB supports it; its token rules differ, which does not matter while
  nothing queries it.
- One table without any key: `module_user_additional_data` (a user's value of an
  additional form field). The migration gives it the primary key
  `(userId, fieldId)` — the pair the code reads and writes by — dropping
  duplicates. `module_pressarticle_archive` and `module_zxprod_meta` have no
  primary key but a unique key on NOT NULL columns, which InnoDB clusters by.
- No `LOCK TABLES`, `REPAIR TABLE` or `OPTIMIZE TABLE` in the code, and no
  `COUNT(*)` without a `WHERE` on the large tables.

## Differences to watch

- `COUNT(*)` without a `WHERE` reads the whole index on InnoDB (MyISAM keeps the
  number). The code runs none on the large tables; new code should not either.
- InnoDB takes more disk space (roughly ×1.5–2).
- Long-running cron and import requests with autocommit behave as before; only
  code that opens a transaction holds locks.
- `PDO::ATTR_PERSISTENT` connections: a request that dies inside a transaction
  must not leave it open for the next one — the plan executor and the tests
  always commit or roll back in `finally`.
- The crashed-table repairs local MyISAM sometimes needs go away.

## Steps

1. On production: list engines, sizes and the MariaDB/MySQL version; check free
   disk space.
2. Migration `db/migrations/2026.10.23 - innodb.sql`: the primary key for
   `module_user_additional_data`, then one anonymous block that converts every
   table still on MyISAM, smallest first, read from `information_schema` — so it
   fits whatever the server has and can be re-run. Needs MariaDB 10.3+.
   Engine change copies the table and blocks writes for its duration — run it in
   a quiet hour (`structure_links` is the long one).
3. Local run: 12.5 s for 70 tables; MyISAM data grew from 322 MB to about
   470 MB on InnoDB. `tests/Integration/DatabaseEnginesTest` checks that no
   table is left on another engine and that every table has a primary or unique
   key. Unit suite green; pages and APIs answer as before. (The full search
   takes about 5 s locally, but issues no SQL at all during that time — it is
   not the database.)
4. Production run with a fresh backup at hand.
5. Then: transactions in `PlanExecutor` ([backend.md](backend.md#transactions)).
   The integration tests already run in a transaction they roll back
   ([testing.md](../testing.md#import-golden-master)).

## Checklist

- [ ] Production: engines, sizes, server version, free space
- [x] Find `COUNT(*)` without `WHERE` on large tables
- [x] Migration: engine change for all MyISAM tables + primary key for `module_user_additional_data`
- [x] Local run: timing, tests, smoke check
- [ ] Manual check of the site in the browser
- [ ] Production run
- [ ] Update [backend.md](backend.md#transactions): transactions available
