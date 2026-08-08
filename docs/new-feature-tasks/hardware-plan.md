# Hardware — Implementation Plan

Plan for [hardware.md](hardware.md). Build order: **Task 1** (catalog table + management section) →
**Task 0** (auto-fill, writes to releases) → **Task 2** (prod-level hardware).
Task 0 must run before Task 2's data migration, so the codes it derives take part in the
per-prod intersection instead of needing a second pass. The import rerouting of **D.7** must ship
*together with* Task 2's migration, not after it — see Part E.

Decisions taken from the task review (Part F holds the reasoning):
- Auto-fill writes to the **release**, not the prod.
- Link tables store a numeric **`hardwareId`**; codes are resolved through the catalog table.
- The catalog stores **`name` + `shortName` per language**.
- Ambiguous formats are resolved by **format × machine family**, sibling-release inference as the
  fallback, and "add nothing" as the default. Missing hardware codes get added.

Every fact and line number below was verified against the working tree and the local database.

---

## Part A — Current state (verified)

### A.1 Storage
- `engine_module_zxrelease_hw_required` — `id`, `elementId`, `value` **ENUM(120 listed / 119 unique)**,
  **MyISAM**, `KEY elementId (elementId, value)`. **101 702 rows** (the 137 624 on the table is its
  `AUTO_INCREMENT`, not a row count).
- Read/written by the generic `DBValueSetDataChunk`
  (`trickster-cms/cms/modules/dataChunks/DBValueSet.class.php`), declared at
  `project/modules/structureElements/zxRelease/structure.class.php:141`.
- Only releases store hardware. A prod **derives** it: `zxProdElement::getHardware()`
  (`project/modules/structureElements/zxProd/structure.class.php:882`) runs the release→prod filter
  conversion and `DISTINCT`s the release rows.
- `DatabaseTable::ZxReleaseHardware` — `project/core/ZxArt/Shared/DatabaseTable.php:27`.

**Data hazards in that table** (all must be handled by the migration):
- **3 638 orphan rows** whose `elementId` matches no `engine_module_zxrelease` row.
- **189 duplicate `(elementId, value)` pairs.**
- 0 rows with `value = ''`, so nothing was ever blanked by the `defender` bug (A.2).

### A.2 The duplicated ENUM value blocks all DDL — fix it first
The ENUM lists `aymouse` **twice** (positions 8 and 94; only position 8 has rows). MariaDB
refuses to rebuild the column while that is true. Verified on MariaDB 11.8.6:

```
CREATE TABLE zzz LIKE engine_module_zxrelease_hw_required;
  → ERROR 1291 (HY000): Column 'value' has duplicated value 'aymouse' in ENUM
ALTER TABLE engine_module_zxrelease_hw_required ADD COLUMN x int NULL;
  → ERROR 1291
ALTER TABLE engine_module_zxrelease_hw_required ENGINE=InnoDB;
  → ERROR 1291
```
`mysqldump`/reload of this table fails the same way. The one statement that **does** work is a
`MODIFY` that rewrites the column definition and thereby drops the duplicate — verified end to end
on the live local table, 101 702 rows preserved, 0 blanked:

```
ALTER TABLE engine_module_zxrelease_hw_required MODIFY value VARCHAR(32) NOT NULL;
```
After that, `ENGINE=InnoDB`, `ADD COLUMN` and `DROP COLUMN` all succeed. This is therefore the
mandatory first statement of the Task 1 migration (B.1 step 0).

*(While verifying this I ran the `MODIFY` on the local dev database and restored the column to a
de-duplicated ENUM. Local data is unchanged — 101 702 rows, 0 blanks — but the local column
definition now lists `aymouse` once. Production is untouched.)*

### A.3 Code lists and the drift between them
| Source | Count | Contents |
|---|---|---|
| DB `ENUM` | 120 listed / 119 unique | 118 shared + `covox`, `zxevolution`; **no `defender`**; `aymouse` twice |
| `ZxArt\Hardware\HardwareItem` | 118 | includes `defender` |
| `ZxArt\Hardware\HardwareCatalog` | 118 | identical to the enum class — no drift here |
| SPA `hardware.*` (en/ru/es) | 120 | 118 + `hdd`, `zxevolution` |
| SPA `hardware-short.*` | 121 | 120 + `alf1` |
| DB translations `hardware` | 120 `item_*` (149 children) | legacy/back-end labels |
| DB translations `hardware_short` | 120 `item_*` | **`item_timex_cartridge` is absent** |

Consequences:
- **`defender` cannot be stored.** It is offered by the release form (it is in `HardwareItem` and
  in the `CONTROLS` group) but the ENUM has no such value. `sql_mode` is `STRICT_TRANS_TABLES`,
  which does not apply to MyISAM, so it would insert as `''` with a warning — there are 0 such
  rows today, so no cleanup is needed, but the bug is real.
- **`zxevolution`** has 4 rows and no `HardwareItem` case, so `HardwareCatalog::getItemGroup()`
  returns `null` and the code vanishes from `ZxProdsList::getHardwareSelector()` (:426) — it cannot
  be filtered on. (It also drops out of `zxReleaseElement::getHardwareMap()`, but that method has
  no callers.) It is also hardcoded in `ZxSoft::$extendedGraphics` and `TslabsManager`, so it is a
  live code that must be seeded.
- `covox` (0 rows), `hdd` and `alf1` (SPA-only keys) are dead and must not be seeded.
- The seed cannot rely on the DB `hardware_short` group as a complete fallback —
  `item_timex_cartridge` is missing there.

### A.4 Groups
`ZxArt\Hardware\HardwareGroup`: `computers`, `storage`, `dos`, `sound`, `controls`, `expansion`.
Group order in `HardwareCatalog::getGroupedItems()` is the display order everywhere.

### A.5 Where hardware names come from today
1. **SPA i18n** — `hardware.<code>`, `hardware-short.<code>`, `hardware-group.<code>` in
   `ng-zxart/src/assets/i18n/{en,ru,es}.json`. Used by every Angular view.
   **Short ≠ full for 40 of 120 codes** (`zx48` → "ZX Spectrum 48K" / "48"); the same 40 in all
   three languages.
2. **DB translations** — `hardware.item_<code>`, `hardware_short.item_<code>`, used only by:
   - `zxReleaseElement::getHardwareInfo()` (zxRelease/structure.class.php:832) and `getLdJsonScriptData()` (:679)
   - `zxProdElement::getHardwareInfo()` (zxProd/structure.class.php:911) and `getLdJsonScriptData()` (:825)
   - `zxProdDataResponseConverter` `hardwareString` (zxProd.class.php:146) — feeds the `ai` preset
   - `project/templates/simple/zxRelease.details.tpl:40`

### A.6 Filtering
- `project/modules/queryFilters/zxReleaseHardware.class.php` — required type `zxRelease`.
- Catalogue: `ZxProdsList::getFilters()` sets `$filters['zxReleaseHardware']` from `hw`
  (ZxProdsList.php:130); `getHardwareSelector()` (:410) builds the grouped selector from the rows
  present in the current result set.
- **`ZxProdsList` runs in two modes.** `getProds()` (:76-78) switches to
  `setExportType('zxRelease')` / `setResultTypes(['zxRelease'])` when `getReleasesValue()` is true,
  reusing the same `$filters` array. Any change to the `hw` filter's required type affects both modes.
- Public API: `/api/…filter:zxReleaseHardware=zx48` is a **documented public contract**
  (`project/core/ZxArt/Content/pages/api.{en,ru,es}.html:618`) taking and returning **codes**.
- Chaining happens in `QueryFiltersManager`: each filter declares one required type; results are
  converted between types by `*QueryFilterConverter`. **Two filters cannot be OR-ed** in that chain.

### A.7 Every other consumer of raw codes
Release-scoped, reading `$this->hardwareRequired` directly:
- `EmulatorResolverService::resolveEmulator()` via zxRelease/structure.class.php:348 → drives
  `isPlayable()` (:555) and `getEmulatorType()` (:956). Hardcodes `zx80`/`zx81`/`tsconf`/`samcoupe`/
  `zxnext` and `UNSUPPORTED_HARDWARE = ['gs']`.
- `ArchiveFileResolverService::filterArchiveFiles($structure, $hardwareCodes)` via :336 → feeds the
  `playableFiles` and `archiveFiles` presets (`dataResponseConverters/zxRelease.class.php:36,83`).
- The runnable consts at zxRelease/structure.class.php:82-91.

Shared / prod-scoped:
- **`project/core/ZxSoft.php:10-21`** — `getListImagePreset()` calls `getHardwareInfo()` on **both**
  prod and release to choose `full` vs `prodListImage` screenshots, keyed on
  `$extendedGraphics = ['zxevolution','zxnext','elementzxmb','zxuno','baseconf','tsconf','gmx']`.
- `HardwareCompatibilityRules` — code → machine-family map, reused by Task 0. It maps a `zx81` code
  that exists in no enum (dead entry; the individual `zx811`/`zx8116`/… codes are already listed).
- `StatsRepository:133-161` joins `DatabaseTable::ZxReleaseHardware`; the model list comes from
  `HardwareCatalog::getGroupItems(COMPUTERS)` (`StatsService:479`).
- Importers writing codes — audited one by one in **D.7**: `ZxdbImport`, `PouetImport`,
  `WorldOfSamImport`, `VtrdosImport` (+ `VtrdosHardwareProvider`), `ZxaaaManager.class.php:62,337-338`
  (derives `hardwareRequired` from file extensions via `$extWords` — a second, pre-existing
  implementation of Task 0's rules), `TslabsManager.class.php:142` (hardcodes `['zxevolution','tsconf']`).
  They all funnel through one line, `ProdsService::updateRelease()` :776-777.
  `HardwareCompatibilityService` does not write codes but compares them, to decide whether an
  imported prod matches an existing one (`ProdResolver:72`) — the D.7 sharp edge.
- `HardwareProvider` trait (`project/core/HardwareProvider.php`) — `use HardwareProvider` appears in
  exactly **two** places: `ZxProdsList.php:13` and `zxRelease/structure.class.php:59`.
  **`zxProdElement` does not have it** — see D.2.

**Pre-existing bug:** `zxProdElement::getHardwareInfo()` (:911-919) caches for 24 h under
`'hw' . $languageId`, **omitting `$short`** — unlike the release version (:839,
`'hw' . ($short?'s':'f') . $languageId`). So `hardwareString` → `getHardwareInfo(false)`
(zxProd.class.php:146-150), which feeds the AI preset via `ProdQueryService:76`, can be served
short labels. Fix it in the same pass.

### A.8 Name collision warning
`ZxArt\Hardware\Services\HardwareService`, `ZxArt\Hardware\Repositories\StorageRepository` and
`ZxArt\Hardware\Dto\HardwareDto` have **nothing to do with this feature**: they store hardware
*devices mentioned in press articles* in `engine_hardware_storage` (AI press parser,
`ArticleParsedDataUpdater::storeHardware()`). Do not extend, rename or reuse them. New classes must
avoid those three names — use `HardwareCatalogService`, `HardwareRepository`, `HardwareItemDto`.
The new catalog table `engine_hardware` is distinct from the existing `engine_hardware_storage`.

### A.9 Volumes
- releases 104 019 (71 213 with hardware, 84 713 with a format); prods 56 339 (53 426 with releases,
  36 641 with ≥1 `original`, 17 429 with >1 release)
- release types: `original` 52 576, `unknown` 24 299, `adaptation` 11 288, `rerelease` 6 454, `crack` 6 037, …

---

## Part B — Task 1: hardware catalog in its own table + management section

The SPA section the task calls "admin" lives at **`/manage`**, not `/admin` — see B.9. Its pages,
folders and services are named `manage-*` throughout.

### B.1 Migration `db/migrations/2026.08.08 - hardware catalog.sql`

**Step 0 — unblock DDL (mandatory first statement, see A.2):**
```sql
ALTER TABLE engine_module_zxrelease_hw_required MODIFY value VARCHAR(32) NOT NULL;
```

**Step 1 — clean the table:**
```sql
DELETE l FROM engine_module_zxrelease_hw_required l
  LEFT JOIN engine_module_zxrelease r ON r.id = l.elementId
  WHERE r.id IS NULL;                                   -- 3 638 orphans
DELETE a FROM engine_module_zxrelease_hw_required a
  JOIN engine_module_zxrelease_hw_required b
    ON b.elementId = a.elementId AND b.value = a.value AND b.id < a.id;  -- 189 dup pairs
```

**Step 2 — the catalog.** Names go into a row-per-language child table, matching how the CMS models
multi-language module data (`engine_module_language` has `PRIMARY KEY (id, languageId)`).
```sql
engine_hardware
  id        int unsigned AUTO_INCREMENT PK
  code      varchar(32)  NOT NULL, UNIQUE KEY
  category  enum('computers','storage','dos','sound','controls','expansion') NOT NULL
  position  int          NOT NULL DEFAULT 0     -- preserves the current catalog display order
  KEY (category, position)

engine_hardware_name
  hardwareId int unsigned NOT NULL
  languageId int          NOT NULL              -- 2105 eng, 930 rus, 84102 spa
  name       varchar(255) NOT NULL              -- replaces hardware.item_<code>
  shortName  varchar(255) NOT NULL              -- replaces hardware_short.item_<code>
  PRIMARY KEY (hardwareId, languageId)
  FOREIGN KEY (hardwareId) REFERENCES engine_hardware(id) ON DELETE CASCADE
```
Both InnoDB, `utf8mb4`. Seed: the 118 `HardwareItem` codes with `category`/`position` from
`HardwareCatalog`, plus the additions of B.2. `covox`, `hdd`, `alf1` are not seeded. Names come from
`ng-zxart/src/assets/i18n/{en,ru,es}.json` (the richer, maintained set); the DB `hardware` /
`hardware_short` groups fill gaps, except `hardware_short.item_timex_cartridge`, which exists in
neither and must be written by hand. The INSERTs are generated once and written into the migration
literally, so the production run is pure SQL.

**Step 3 — normalize the link table to ids:**
```sql
ALTER TABLE engine_module_zxrelease_hw_required ENGINE=InnoDB;
ALTER TABLE engine_module_zxrelease_hw_required
  ADD COLUMN hardwareId int unsigned NOT NULL DEFAULT 0 AFTER elementId;
UPDATE engine_module_zxrelease_hw_required l
  JOIN engine_hardware h ON h.code = l.value SET l.hardwareId = h.id;
-- gate: must return 0 before continuing
SELECT COUNT(*) FROM engine_module_zxrelease_hw_required WHERE hardwareId = 0;
ALTER TABLE engine_module_zxrelease_hw_required
  DROP KEY elementId, DROP COLUMN value,
  ADD UNIQUE KEY elementId (elementId, hardwareId),
  ADD KEY hardwareId (hardwareId),
  ADD FOREIGN KEY (hardwareId) REFERENCES engine_hardware(id);
```
The `SELECT` gate catches any code missing from the seed before the `DROP COLUMN` makes it
unrecoverable. `RESTRICT` on the FK makes "cannot delete hardware in use" a database guarantee, not
only an application check. The `UNIQUE` key is safe only after step 1's de-duplication — verify
`DBValueSetDataChunk::persistExtraData()`'s delete-leftovers diff still behaves under it (B.3).

The id column also retires the `defender` bug permanently.

### B.2 Codes to add to the catalog
| code | category | why | written by a rule? |
|---|---|---|---|
| `defender` | controls | in `HardwareItem`, offered by the release form, unstorable today | no — manual |
| `zxevolution` | computers | 4 live rows, no catalog entry; also hardcoded in `ZxSoft`/`TslabsManager` | no — manual |
| `samdos` | dos | Sam Coupe disk releases carry **no** DOS today (of 1 094 `samcoupe` releases: `cpm` 20, `atom` 8, `disciple` 1) | yes — C.3/C.4 |
| `mb02` | storage | the `mbd` format has 50 releases and no MB-02+ code exists | yes — C.3 |
| `gdos` | dos | `mgt` is G+DOS; DISCiPLE and +D are *alternative* interfaces sharing it, so the determinable fact is the DOS, not the interface (see C.4) | yes — C.4 |

`didaktik40` is **not** added: no rule would write it (C.3 assigns only `mdos` for `d40`/`d80`), and
an unused code is drift. `covox`, `hdd`, `alf1` are dropped.

### B.3 The chunk: ids in storage, codes in the element
Everything above the database — the element property, the form, `/formdata/`, the REST DTOs, the
public API, the imports, `EmulatorResolverService` — works in codes and must keep doing so. Only the
two link tables change.

Extend the generic CMS chunk (docs/cms.md: add logic to CMS base classes, do not subclass in the
project package) with an optional code lookup:
```php
$moduleStructure['hardwareRequired'] = [
    'DBValueSet',
    [
        'tableName'       => $this->dataResourceName . '_hw_required',
        'valueField'      => 'hardwareId',
        'lookupTable'     => 'hardware',
        'lookupIdField'   => 'id',
        'lookupCodeField' => 'code',
    ],
];
```
**Blast radius is small:** `DBValueSet` has exactly four declarations repo-wide —
`zxRelease.releaseFormat` (:136), `zxRelease.hardwareRequired` (:141), `zxRelease.language` (:148),
`zxProd.language` (:155). An *optional* `lookupTable` key touches none of the other three. Note the
two `language` fields share one table (`zxitem_language`) across two element types, so the 119-row
map cache must key on the **lookup table name**, not the chunk instance.

**Ordering requirement:** `persistExtraData()` diffs `$this->storageValue` against `getRows()` with
`$row[$this->valueField] == $value`. With `valueField => 'hardwareId'`, `getRows()` yields ids while
`storageValue` holds codes. The code→id mapping must happen **before** that diff loop, or every save
deletes and re-inserts every row. Unknown codes are dropped, never written as `0`.

`copyExtraData()` in that chunk is dead code — `structureElement::copyExtraData()` gates on
`$dataChunk instanceof ElementStorageValueHolderInterface` (structureElement.class.php:1329), which
`DBValueSetDataChunk` does not implement, and the release clone copies the field explicitly
(`zxRelease/action.clone.class.php:31`). No change needed there.

### B.4 Backend catalog service
New in `project/core/ZxArt/Hardware/`:
- `Repositories/HardwareRepository.php` — `readonly final`, extends
  `ZxArt\Shared\Repositories\AbstractRepository`, `Connection` injected. `getAll()`, `getByCode()`,
  `insert()`, `update()`, `delete()`, `codeExists()`, `countUsages(int $hardwareId)`. Query Builder
  only, no raw SQL (docs/php/repositories.md).
- `Dto/HardwareItemDto.php` — `id`, `code`, `category` (`HardwareGroup`), `position`,
  `names: array<int, HardwareNameDto>` keyed by languageId; `Dto/HardwareNameDto.php` —
  `languageId`, `name`, `shortName`.
- `Rest/HardwareItemRestDto.php`, `Rest/HardwareNameRestDto.php`, `#[Map(target: …)]` on the Dto side.
- `Exception/HardwareException.php` — message + status code, like `PlaylistException`.
- `HardwareCatalogService.php` — per-request cache over the repository:
  `getGroupedCodes()` (drop-in for `HardwareCatalog::getGroupedItems()`), `getCategoryOf()`,
  `getIdsByCodes(string[]): int[]`, `getLabels(int $languageId)`, plus validated create/update/delete.
- `DatabaseTable::Hardware = 'hardware'`, `DatabaseTable::HardwareName = 'hardware_name'`.

`HardwareItem` (the PHP enum) is **kept** but demoted: it stays the compile-time list that
`EmulatorResolverService`, `HardwareCompatibilityRules`, `StatsService` and the runnable consts need
as constants, not editable rows. `HardwareCatalog` and the `HardwareProvider` trait become thin
delegates to `HardwareCatalogService`, so existing call sites keep their shape. A code added through
the management screen is immediately storable and displayable; it only lacks emulator/compatibility/stats
behaviour, which is correct — that behaviour is code, not data.

### B.5 Naming pipeline flip (Task 1.2)
Every response carrying a hardware code starts carrying its `name`, `shortName` and `category` for
the request language (`X-Language` → `LanguagesManager`). Group names stay SPA-owned
(`hardware-group.*`) because the category set is a hardcoded enum with no management form
(Part F.5).

`ProdHardwareInfoDto` / `ProdHardwareInfoRestDto` grow from `{id}` to
`{id, name, shortName, category}`. That single pair is shared by prod core, prod releases and
release details, so it is the one place to widen. `ProdInfoBuilder::buildHardware()` becomes the
only builder and takes labels from `HardwareCatalogService`.

**Full checklist — every place hardware appears in the SPA, with its backend.** This is also the
answer to Task 2.2; Part D says what changes per row.

| # | Angular file | What it renders | Backend that feeds it |
|---|---|---|---|
| 1 | `entities/zx-prod-block/zx-prod-block.component.html:30-39` | `hardware-short.<code>` badges on a prod card | `ProdsTransformer::buildHardwareInfo()` → `ProdDto`/`ProdRestDto.hardwareInfo`; legacy `zxProd.class.php` `list` preset |
| 2 | `entities/zx-prod-release-card/zx-prod-release-card.component.html:20-27` | `hardware-short.<code>` badges on a release card | `ProdReleaseDto`/`ProdReleaseRestDto.hardware` ← `ProdReleasesService` + `ProdInfoBuilder::buildHardware()` |
| 3 | `features/prod-details/components/zx-prod-release-row/zx-prod-release-row.component.html:65-70` | release table cell: icon + short name, links to `/prods?hw=` | same as #2 |
| 4 | `features/prod-details/components/zx-prod-releases-section/zx-prod-releases-section.component.html:99` | `release-row.col-hardware` column header | — (own i18n key) |
| 5 | `features/release-details/components/zx-release-hero/zx-release-hero.component.html:25-27` | full-name chips on the release page | `ReleaseDetailsService` → `ReleaseDetailsRestDto.hardware` (+ `prodHardware`, D.2) |
| 6 | `entities/zx-prods-category/zx-prods-category.component.ts:316-330` (`buildHardwareSelector`) | catalogue filter: `hardware-group.<group>` + `hardware.<code>` | `ZxProdsList::getHardwareSelector()`, exposed by `zxProdCategory.class.php:31` and `zxProdCategoriesCatalogue.class.php:33` |
| 7 | `entities/zx-prods-category/zx-prods-category.component.html:81-85` | the `zx-multi-select-filter` for `hw` | same as #6 |
| 8 | `entities/zx-prods-category/zx-prods-category.component.ts:531-545` | "select whole group" helper | same as #6 |
| 9 | `entities/zx-prods-category/models/zx-prod-category.ts:22,39` | the `hardwareSelector` model class | same as #6 |
| 10 | **`shared/components/zx-prod-component.ts:9-20`** | base class with `@Output() hardwareChanged` / `hardwareClicked()` — clicking a badge on a card filters by it; wired at `zx-prods-category.component.html:125,151` | same as #6 |
| 11 | `pages/release-edit/release-edit-page.component.ts:108-109,197,213-223` + `.html:63-65` | release form hardware multi-select | `Formdata::enumSpecs()['zxRelease']['hardwareRequired']` → `HardwareProvider::getHardwareList()`, currently `clientLabels: true` + `mode: 'grouped'`, which **flattens the groups away** |
| 12 | `features/stats/components/zx-stats-category/zx-stats-category.component.ts:266-271` | `hardware-short` prefix for the hardware chart | `StatsService:479` + `StatsRepository:133-161` |
| 13 | `shared/ui/zx-hardware-icon/zx-hardware-icon.component.ts` (+ `.scss`, `shared/theme/_zx-hardware-icon.theme.scss`, `assets/svg/hw-*.svg`) | code → SVG icon | none — stays frontend |
| 14 | `features/prods-browser/services/prods-browser.service.ts:19,77` | passes `hardwareInfo` to prod cards | `Prodlist` controller → `ProdsTransformer` |
| 15 | `features/prod-details/services/prod-related-prods-api.service.ts:71` | stubs `hardwareInfo: []` | `ProdCompilationItems`/`ProdCompilations`/`ProdSeries*` |

Backend-only consumers to convert alongside: `dataResponseConverters/zxRelease.class.php`
(`hardwareRequired` at 30/105/170, `hardwareInfo` at 55-58), `zxProd.class.php`
(`hardware`/`hardwareInfo`/`hardwareString` at 63-67, 146-150), `ZxSoft::getListImagePreset()`,
`zxProdElement`/`zxReleaseElement` `getLdJsonScriptData()`, `templates/simple/zxRelease.details.tpl:33-40`.

DTO/model files to widen: `features/prod-details/models/prod-core.dto.ts`,
`features/prod-details/models/prod-release.dto.ts`,
`features/release-details/models/release-details.dto.ts`,
`shared/models/zx-prod-dto.ts` + `shared/models/zx-prod.ts` (`ZxProdHardwareItems`),
`entities/zx-prods-category/models/zx-prod-category-dto.ts`,
`shared/models/form-data-response.ts` (grouped enum options).
`release-details.dto.ts:54` and `prod-release.dto.ts:25` also carry a raw `hardwareRequired: string[]`
beside `hardware` — decide per DTO whether it stays (it is what the edit form round-trips) or goes.

i18n: keep `hardware-group.*`; **delete** `hardware.*` and `hardware-short.*` from `en/ru/es.json`
once every consumer reads names from the response; keep one `hardware.unknown` fallback.

The 240 `hardware`/`hardware_short` DB translation elements become dead and can be removed in a
follow-up migration — not this one, they are the fallback while the seed is verified.

`zx-multi-select-filter` already supports grouped options (`[groups]` + `MultiSelectGroup`), so the
release form can finally show hardware grouped by category once `enumSpecs` stops flattening it.

### B.6 Icons for new codes
`zx-hardware-icon.component.ts` already degrades gracefully:
`return HW_ICON_MAP[this.id] ?? 'hw-computer'`. A new code therefore renders a generic icon rather
than breaking, and **no category-driven fallback should be added** — the existing map deliberately
sends DOS-group codes (`disciple`, `opd`, `3dos`) to `hw-disk`, which a category rule would
contradict. Just add explicit entries for the B.2 codes, using the real icon names
(`gamepad` for controls, not `hw-joystick`, which does not exist).

### B.7 Privilege `editHardware` (Task 1.3)
- Add `editHardware` to the `root` module's action list. `privilegesManager::scanDirectory()`
  (privilegesManager.class.php:208-244) is called once **per include path** and `include_once`s each
  package's `structure.actions.php` separately, merging into `moduleActionsList["root:$action"]` — so
  a project-package file containing only `$moduleActions[] = 'editHardware';` is sufficient and need
  not repeat the CMS list. Per docs/cms.md ("modify CMS files directly, do not create workaround
  wrappers in `project/`"), the cleaner move is to add the line to
  `trickster-cms/cms/modules/structureElements/root/structure.actions.php` itself.
- There is **no** `action.editHardware.class.php`. The privilege is a flag; the new controller checks
  it directly (Task 1.7 forbids the legacy action system).
**Grant it to the content managers in the migration.** Privileges are held by user *groups*
(`structureType = 'userGroup'`), and `content_managers` is element **478**. The relevant precedent:
478 already holds public-root-scoped content privileges on element 1 — `authorAlias.publicAdd` /
`publicReceive` / `publicDelete` / `showPublicForm`, `file.*`, `zxMusic.submitTags` — so a
public-root `root:editHardware` row for the same group is exactly in line with how content editing
is already granted. (Its *other* root-module rows — `deleteElements`, `moveElements`, … — sit on
element **12**, `admin_root`, which is the legacy admin panel and not what Task 1.3 asks for.)

The public root is element **1**, `marker = 'public_root'` (`rootMarkerPublic` in
`project/config/main.php:19`); element 12 is the admin root. Resolve both ends by marker and group
name rather than hardcoding ids, so the statement is self-documenting and safe to re-run:

```sql
INSERT INTO engine_privilege_relations (privilegeId, elementId, type, userId, module, action)
SELECT 0, root.id, 1, grp.id, 'root', 'editHardware'
FROM engine_structure_elements root
JOIN engine_structure_elements grp
  ON grp.structureType = 'userGroup' AND grp.structureName = 'content_managers'
WHERE root.marker = 'public_root'
  AND NOT EXISTS (
    SELECT 1 FROM (SELECT * FROM engine_privilege_relations) existing
    WHERE existing.userId = grp.id AND existing.elementId = root.id
      AND existing.module = 'root' AND existing.action = 'editHardware'
  );
```
(`privilegeId` defaults to 0 and is unused by `privilegesManager`; the `SELECT * FROM …` wrapper in
the `NOT EXISTS` is the same MySQL self-insert workaround the press-article migration uses.)

The `developers` group (480) needs no row — it already carries the full admin-root set and its
members are the people who would edit the catalog by hand anyway; add it if you want them to see the
SPA section without a re-login. Consider `catalogues-managers` (1113) too, if hardware upkeep
belongs to them rather than to content managers.

**`privilegesManager::getUserPrivileges()` reads `$user->privileges`, which is session-cached**, so
this grant does not take effect for a logged-in user until they re-log in or `refreshPrivileges()`
runs. Note it in the migration comment — otherwise the first tester reports the section as broken.

**Where the SPA learns it has the privilege (the Task 1.4 question):** not `/currentuser/` —
`CurrentUserRestService::buildDto()` returns `{id, userName, authorId}` and knows nothing about
privileges. Use the existing generic endpoint:
`/element-privileges/?id={publicRootId}&privileges=editHardware` via `ElementPrivilegesApiService`.
Verified that this works: `privilegeChecking` is never disabled for `publicStructureManager`, so
`getElementById(rootId)` runs `compileElementPrivileges()` (structureManager.php:1044/1264) and
populates the map; `manufactureElementsObject()` requires `isset($elementPrivileges['root'])`, which
holds for everyone because user-group 487 carries `root:show` on the root element; and
`checkPrivilegesForAction($rootId, 'editHardware', 'root')` then reads the compiled map correctly.
The only missing piece is the root id on the client — add `publicRootId` to `CurrentUserRestDto`
from `structureManager::getRootElementId()`.

### B.8 Backend controller (Task 1.7)
`project/core/ZxArt/Controllers/HardwareData.php` → `ZxArt\Controllers\HardwareData`, reachable at
`/hardware-data/`. Auto-discovered from the URL segment by `controller::detectApplication()`
(controller.class.php:324-338, `toPascalCase('hardware-data') === 'HardwareData'`) — no registration
anywhere. Verified `/hardware-data/` is currently free (404). `PlaylistsData` is the template to copy.

- extends `LoggedControllerApplication`, `rendererName = 'json'`, `initialize()` starts the `public`
  session and creates the renderer, `getUrlName()` returns `''`.
- **One privilege check for all write actions** at the top of `execute()`: `editHardware` on the
  public root via `ElementPrivilegesService`, else 403. `GET` is not gated — the catalog is public
  data the filters and forms need anyway.
- `GET` → the full catalog in all three languages (the management form edits all at once).
- `POST ?action=create|update|delete` with a JSON body, `match($action)` dispatch, returning the
  refreshed catalog, exactly as `PlaylistsData` does.
- Validation in `HardwareCatalogService`: non-empty unique `code` matching `[a-z0-9_+]+`, known
  category, name and short name for every public language. `delete` refuses while the code is
  referenced and reports the count; the FK enforces it at the DB level too.
- `HardwareException` caught and mapped to its status code.
- OpenAPI: new `api/hardware-data.yaml`; amend `api/prod-details.yaml` (`ProdHardwareInfo`),
  `api/prod-releases.yaml`, `api/release-details.yaml`, `api/form-data.yaml`,
  `api/author-details.yaml`, the current-user spec for `publicRootId`, and `api/prod-list.yaml`,
  which has no hardware schema at all despite `ProdsTransformer` sending `hardwareInfo`.

### B.9 Frontend management section (Tasks 1.4–1.6)
**The section lives at `/manage`, not `/admin`.** `/admin` is not available:
`controller::detectApplication()` resolves `/admin/*` to the legacy Smarty admin application
(`trickster-cms/cms/modules/applications/admin.class.php`) before anything else, and `SpaRouter` is
only consulted from `trickster-cms/homepage/modules/applications/public.class.php:48`, which
`/admin/*` never reaches — verified: `curl http://zxart.loc/admin/hardware` → 200,
`<title>Administration</title>`. `/manage` is free (404 today, no application or controller of that
name). So: `/manage` → redirect to `/manage/hardware`, add `#^/manage(/[a-z0-9-]+)?/?$#` to
`SpaRouter::ROUTE_PATTERNS` plus a `SpaRouterTest` case.

- Routes in `ng-zxart/src/app/app.routes.ts`: both lazy `loadComponent`, both with a `titleKey`
  (a routed non-entity page without one gets no breadcrumbs) and the new guard.
- `shared/guards/root-privilege.guard.ts` — sibling of `edit-privilege.guard.ts`: reads
  `route.data['privilege']`, takes the public root id from `CurrentUserService`, asks
  `ElementPrivilegesApiService`, redirects to `/` when denied.
- Menu: `shared/navigation/menu.config.ts` is hardcoded and `MAIN_MENU` has no conditional support.
  Put the entry in the header/user menu beside the profile link, gated on the same privilege
  observable, rather than teaching `MenuEntry` about privileges.
- `pages/manage/manage-page.component.*` — `zx-page-layout`, one `<h1 zxPageHeader>`, `zx-tabs`
  shell holding the single `hardware` sub-section so later sections drop in.
- `pages/manage-hardware/…` — list grouped by category in `HardwareGroup` order, one section per
  group with its `hardware-group.<code>` heading; rows show code, three names, three short names and
  the usage count. "Add" button in the page header.
- `pages/manage-hardware-edit/…` — create/edit form built exactly like `pages/author-edit` and
  `pages/release-edit` (the reference forms): `ZxFormDirective`, `zx-form-section`,
  `zx-form-field`/`zx-form-label`/`zx-form-control`, `zx-input` (code + 6 name fields), `zx-select`
  for the category, `zx-control-errors`, `zx-form-actions`, `ZxDeleteEntityButtonComponent` behind
  `ConfirmDialogService`. Reactive form, `OnPush`, `markForCheck` per docs/angular.md.
- `features/manage-hardware/services/manage-hardware-api.service.ts` — `BehaviorSubject` store over
  `/hardware-data/`, `shareReplay`, `catchError` in the service, mutations via `tap(next)`.
- i18n keys in `en.json`, `ru.json`, `es.json`. `composer run build` after any `ng-zxart/` change.

### B.10 Tests
`HardwareCatalogServiceTest` (grouping, labels, validation, delete guard), `HardwareRepositoryTest`,
a chunk test for code↔id mapping in both directions including unknown codes and the diff-ordering
case of B.3, a controller test for the privilege gate, the `SpaRouterTest` addition, and a
regression test that the release form round-trips `defender` — the stated payoff of the id column.

---

## Part C — Task 0: auto-fill hardware from the release format

Writes to the **release** (Part F). Rules are **additive and idempotent**: they only add codes, never
remove an editor's choice, and re-saving changes nothing.

### C.1 Why storage/DOS is the real gap
Formats are recorded well; the hardware they imply is not:

| format | releases | already carry the implied code |
|---|---|---|
| `tap` | 21 560 | `tape` — 344 |
| `tzx` | 18 699 | `tape` — 83 |
| `scl` | 28 453 | `betadisk` — 43, `trdos` — 449 |
| `trd` | 5 797 | `betadisk` — 79, `trdos` — 229 |

Machines, by contrast, are almost always already set. Releases carrying an ambiguous format and
**no** `computers`-group code: `dsk` 40 of 2 258, `mgt` 54 of 1 237, `p` 214 of 2 554, `o` 75 of 156,
`tar` 123 of 235; `d80`, `d40`, `mbd`, `sad`, `z81` — **0**. So the machine rarely needs guessing,
and when present it is what disambiguates the format. Hence the staged engine below rather than a
flat format → hardware table.

### C.2 Where it runs
`ZxArt\Releases\Services\ReleaseHardwareAutofillService` — `readonly`, constructor injection, no
DI-context trait. Called from `zxReleaseElement::updateFileStructure()` right after
`$this->releaseFormat` is recomputed (structure.class.php:909-916), before the final
`persistElementData()`.

**Re-saving a release always re-checks the format** — verified, this is exactly the behaviour asked
for and it needs no extra wiring:
- `publicReceiveZxRelease::execute()` calls `updateFileStructure()` **unconditionally** on every
  save (action.publicReceive.class.php:31), not only when a new file was uploaded. The
  `originalName` check above it only decides whether to reset `parsed`.
- `updateFileStructure()` re-parses the archive only when the md5 changed, but it **always**
  recomputes `$this->releaseFormat` from the stored structure and always calls
  `persistElementData()` (:899-919). So a plain metadata re-save still lands on a fresh format list,
  and the auto-fill hook placed just before that final persist always sees the current value.
- The no-file branch early-returns at :890-895 after setting `releaseFormat = []`. The hook sits
  after it, so a release without a file is never touched — correct: no format, no rules.

Verified callers of `updateFileStructure()`: `publicAdd` (:46), `publicReceive` (:31),
`zxProdsUploadForm::batchUpload` (:138), `Crontab.php:673`, `fix.class.php:216`. **Importers are not
among them** — `ProdsService::updateRelease` only sets `parsed = 0` and leaves the parse to the cron
pass, so imported releases get auto-fill on the next cron run rather than at import time. That is
acceptable, but it is not "every import path". See D.7 for the rest of the import story.

`ZxaaaManager.class.php:337-338` already derives `hardwareRequired` from file extensions through its
own `$extWords` map — a second implementation of these rules. Reconcile it with the new service or
it will keep writing its own mapping.

### C.3 Stage 1 — machine-independent rules, always applied

| format(s) | adds | group | evidence |
|---|---|---|---|
| `tap`, `tzx` | `tape` | storage | the only medium these encode |
| `mdr` | `microdrive` | storage | 50 of 219 |
| `trd`, `scl` | `betadisk` | storage | the only interface that reads them |
| `trd`, `scl` | `trdos` | dos | **guarded**, C.5 |
| `fdi`, `udi`, `td0` | `betadisk` | storage | Beta Disk containers |
| `cpm` | `cpm` | dos | 14 of 14 |
| `opd` | `opd` | dos | 53 of 155 (Opus Discovery) |
| `d40`, `d80` | `mdos` | dos | Didaktik disk images; 41 of 184 `d80` and 5 of 30 `d40` already tagged |
| `mbd` | `mb02` (new) | storage | 50 releases, no such code today |
| `mld` | `dandanator` | storage | 8 of 8 |
| `dck` | `timex_cartridge` | storage | 64 of 66 carry `timex2068` |
| `spg` | `tsconf` | computers | 81 of 92 — the format exists only for TSConf |
| `nex` | `zxnext` | computers | 126 of 128 |
| `snx` | `zxnext` | computers | 14 of 14 |
| `sad` | `samdos` (new) | dos | 19 of 19 are `samcoupe`; Sam's disk system has no code today |
| `tar` | `esxdos` | dos | 138 of 235 — `tar` is the esxdos/divMMC SD archive format |

`d40`/`d80` get a DOS but no storage code: the Didaktik drive interface has no code in the catalog
and inventing one that no data supports would be drift.

### C.4 Stage 2 — format × machine family
The machine family comes from `HardwareCompatibilityRules::codesToGroups()` applied to the release's
existing codes. This resolves the formats the task flagged as ambiguous, and it resolves ~98% of
them because the machine is already there.

| format | family | adds |
|---|---|---|
| `dsk` | `zx48` (includes +2/+3) | `3dosdisk` + `3dos` |
| `dsk` | `samcoupe` | `samdos` |
| `mgt` | `zx48` | `gdos` (new) |
| `mgt` | `samcoupe` | `samdos` |
| `p`, `z81` | `zx81` | nothing further — the machine is the information |
| `o` | `zx80` | nothing further |
| any of the above | more than one family, or a family with no row | **nothing** |

`mgt` + `zx48` deliberately adds **`gdos`, not `disciple` + `plusd`**: DISCiPLE and MGT +D are
*alternative* interfaces that share the G+DOS disk format. A release needs one of them, never both,
and the format cannot say which — so the determinable fact is the DOS. Writing both onto ~1 180
releases would be knowingly-false data.

### C.5 Guards
- **The DOS guard applies to every DOS code any stage emits, not just `trdos`.** Skip a DOS addition
  when the release already carries any of `cpm`, `isdos`, `tasis`, `nedoos`, `mdos`, `tos`, `bsdos`,
  `3dos`, `esxdos`, `disciple`, `gdos`, `opd`, `samdos`, `trdos`, `trdos4x`. This matters beyond
  TRD/SCL: `dsk` + `cpm` is 41 releases that would otherwise get `3dos` bolted on, and
  `samcoupe` + `cpm` is 20 that would get `samdos`. The task text carves out CP/M explicitly.
- Storage codes are **not** guarded: `betadisk` is the physical interface whichever DOS runs on it.
- Stage 2 never fires when the release names more than one machine family, or when the family has no
  row for that format. "Add nothing" is always the default.
- Every code is validated against the catalog before it is written, so a rule can never produce an
  unstorable value.

### C.6 Stage 3 — sibling inference, for the residue with no machine at all
Only for releases whose format is in stage 2 and which carry no `computers` code: `dsk` 40, `mgt` 54,
`p` 214, `o` 75 — under 400 releases.

Take the machine families of the prod's other releases (later, once Task 2 lands, also the prod's own
hardware). If exactly **one** family results, apply that family's stage-2 row — the storage/DOS
codes **only**. If zero or more than one family results, add nothing beyond stage 1.

Stage 3 must **not** add a concrete computer code. "Family = `zx48`" spans zx48/zx16/zx128/pentagon/
scorpion/profi/…; picking a representative model would turn a family-level inference into a specific
claim the data does not support. Roughly 400 releases keeping an incomplete-but-correct record is the
right outcome.

### C.7 No rule at all
`bin`, `rom`, `img`, `z80`, `sna`, `szx`, `slt`, `$b`, `$c`. Checked each: `img` spreads over
zx48 38 / zxm 8 / tsconf 5 / sinclairql 5, `$b` over zx48 11 / zxm 8 / pentagon128 5, `bin`/`rom`
over everything. The one arguable case is `$c`, which skews to IDE interfaces (`nemoide` 30,
`smuc` 29, `atmide` 22, `zcontroller` 20, `sdz` 16 of 67 releases) — but no single code dominates,
so "no rule" is right.

### C.8 One-off backfill script (Task 0.3)
A private method in `project/modules/applications/fix.class.php` calling the **same**
`ReleaseHardwareAutofillService` for every release with a format, so the web-triggered backfill and
the on-save behaviour can never diverge. The file already runs under the `crontab` user with
`adminStructureManager` and 2 GB / 1160 s limits.

Note `fix.class.php:39-61` currently hardcodes a single call (`$this->fixReleases()`) with **no
dispatch on a request parameter**. The `?dry=1` / `?offset=` contract below needs a parameter-driven
entry point that does not exist yet — add one, since these are run from the web.

- `?dry=1` printing the diff without persisting, to eyeball a few hundred releases first.
- Resumable `?offset=` and batched id ranges with `echo` progress like the neighbouring methods —
  84 713 element loads will not fit in one 1160 s request.
- Idempotent by construction; a re-run is safe.
- Clear the element cache and the `hardwareInfo` cache key for each touched release.

### C.9 Tests
`ReleaseHardwareAutofillServiceTest`: one case per stage-1 rule, each stage-2 row, the
multiple-families and unknown-family cases, the DOS guard (including `dsk` + `cpm`), stage 3 with
one / zero / several sibling families and its refusal to add a computer code, idempotency, and
empty-format input.

---

## Part D — Task 2: hardware on the prod

### D.1 Storage + element
- Migration `db/migrations/2026.08.08 - prod hardware.sql`:
  ```sql
  CREATE TABLE engine_module_zxprod_hw_required (
    id         int unsigned AUTO_INCREMENT PK,
    elementId  int          NOT NULL,
    hardwareId int unsigned NOT NULL,
    UNIQUE KEY elementId (elementId, hardwareId),
    KEY hardwareId (hardwareId),
    FOREIGN KEY (hardwareId) REFERENCES engine_hardware(id)
  ) ENGINE=InnoDB;
  ```
- `zxProdElement::setModuleStructure()` gains the same `DBValueSet` declaration as the release (B.3;
  `dataResourceName` is `ProdsRepository::TABLE` = `module_zxprod`, so the table name matches), plus
  `@property string[] $hardwareRequired` in the docblock.
- `DatabaseTable::ZxProdHardware = 'module_zxprod_hw_required'`.
- `zxProdElement::getHardware()` changes meaning: the prod's **own** codes. A second method
  `getAggregatedHardwareCodes()` returns own ∪ **its releases'**, for the places that must not
  regress (cards, LD-JSON, `hardwareString`/AI preset, the catalogue selector).

**Two directions, two names — do not conflate them:**

| method | on | returns | used by |
|---|---|---|---|
| `getAggregatedHardwareCodes()` | `zxProdElement` | own ∪ **releases'** (looks down) | prod cards, LD-JSON, AI preset, catalogue selector, stats |
| `getEffectiveHardwareCodes()` | `zxReleaseElement` | own ∪ **prod's** (looks up) | emulator resolution, playable files, list image preset — see D.3 |
| `hardwareRequired` | both | the element's own codes only | edit forms, `publicReceive`, release card/hero display |

Both resolvers live in `ZxArt\Prods\Services\ProdHardwareService`, with the union queries in a
repository, not in the elements.
- Fix the caching bug of A.7 at the same time: `zxProdElement::getHardwareInfo()`'s 24 h key omits
  `$short`, so short labels can leak into the AI preset. Add `$short` to the key **and** fold in the
  prod's own value (or drop the cache on save), or edits appear a day late.

### D.2 Form, action, view
- **`zxProdElement` does not `use HardwareProvider`** (verified: only `ZxProdsList.php:13` and
  `zxRelease/structure.class.php:59` do). `Formdata::buildEnums()` skips any spec whose method is
  missing (`Formdata.php:446-449`), so without adding the trait the prod form's hardware select comes
  back **empty**. Add `use HardwareProvider;` to `zxProdElement` **and** to
  `zxProdsUploadFormElement`, which needs it for the batch field below.
- `zxProd/action.publicReceive.class.php` — add `'hardwareRequired'` to `setExpectedFields()`. Mind
  the `expectedFields` trap (docs/cms.md): a field listed but absent from the request wipes existing
  data, so the Angular form must always submit the key, empty array included.
- `Formdata::enumSpecs()` — add
  `'zxProd' => [… 'hardwareRequired' => ['method' => 'getHardwareList', 'mode' => 'grouped', …]]`
  **and the same entry for `'zxProdsUploadForm'`**, plus the grouped-payload change from B.5 so the
  groups survive.
- `pages/prod-edit/prod-edit-page.component.ts/.html` — a `hardwareRequired` control and the field.
- **Shared component (Task 2.1):** extract `shared/ui/zx-hardware-select/` — a grouped
  `zx-multi-select-filter` with `ControlValueAccessor` over `string[]` — and use it from the prod
  form, the release form and the batch form. Nothing else is duplicated enough to extract: the
  read-side chips and badges genuinely differ in variant and label length, and `zx-hardware-icon` is
  already shared.

**Batch upload form — hardware goes to the prod only.**
`zxProdsUploadFormElement` gains `$moduleStructure['hardwareRequired'] = 'array';` — a plain
transient chunk like its `language`, not a `DBValueSet` (that element's `dataResourceName` is
`module_generic` and it persists nothing of its own). `batchUploadZxProdsUploadForm::execute()` then
copies it onto the created production beside the other shared fields
(`$zxProdElement->hardwareRequired = $structureElement->hardwareRequired;`, next to `->language` at
action.batchUpload.class.php:67) and **not** onto the release it creates per file.

This composes well with Task 0: the uploader states the machine once for the whole batch, it lands
on the prod, and each release created from a file gets its own medium/DOS codes from
`updateFileStructure()` → `ReleaseHardwareAutofillService` (C.2), which the action already calls
(action.batchUpload.class.php:137). The batch form therefore needs no release-level hardware field
at all. The prod form field is not hidden in batch mode (unlike compilation items, series and the
extra file selectors — see docs/domain/prod.md), because the pipeline can apply it.

- **Prod view:** the prod page renders **no** hardware today — `ProdCoreRestDto.hardware` is sent but
  no template reads it. Add a chip row to `features/prod-details/components/zx-prod-hero/`, mirroring
  `zx-release-hero` (`zx-chip variant="mono"`, `zx-hardware-icon`, `href="/prods?hw=<code>"`), fed by
  the existing `core.hardware`, which now means the prod's own set.

**Release view — show the prod's hardware too, separated by chip colour.**
The release hero renders one `zx-inline` row mixing hardware, formats and languages
(zx-release-hero.component.html:25-31), where hardware is `variant="mono"`, formats are
`variant="mono-outline"` and languages are `variant="filled"`. So the inherited/own distinction must
**not** use `variant` — `mono-outline` already means "format" in that same row. Use `color` instead,
which is exactly what the existing chip API is for
(`ZxChipColor = 'neutral' | 'primary' | 'artist' | 'code' | 'intro'`, tokens already themed):

| chips | rendering | meaning |
|---|---|---|
| inherited from the prod | `variant="mono"` (default `color="neutral"`) | what the production needs |
| the release's own | `variant="mono" color="primary"` | what *this* release adds or changes |

Both stay in the `mono` "technical chip" family, so they read as one group and stay distinct from
formats. Order: inherited first, release-specific after — the base requirement, then the deviation.
The release-specific chips carry a translated `title` so the colour is explained on hover; add the
i18n key alongside.

Contract: `ReleaseDetailsDto`/`ReleaseDetailsRestDto` keep `hardware` as the release's **own** set
and gain **`prodHardware`** — the prod's codes minus any the release already lists, so nothing is
drawn twice. Deliberately *not* a flag on `ProdHardwareInfoDto`: that DTO is shared with prod core
and prod releases (B.5), where an "inherited" flag is meaningless.

### D.3 The release resolves its prod's hardware dynamically
These **release-scoped backend consumers read `$this->hardwareRequired` directly** and would
silently return less after D.4 strips `zx80`/`zx8116`/`tsconf`/`samcoupe`/`zxnext` into the prods:

- `EmulatorResolverService::resolveEmulator()` (zxRelease/structure.class.php:348) → `isPlayable()`
  (:555) and `getEmulatorType()` (:956) — **online emulation would stop being offered**
- `ArchiveFileResolverService::filterArchiveFiles()` (:336) → the `playableFiles` and `archiveFiles`
  presets (`dataResponseConverters/zxRelease.class.php:36,83`)
- the runnable consts at :82-91
- `ZxSoft::getListImagePreset()` — screenshot preset selection for prod **and** release lists
- `StatsRepository:133-161` — the per-year computer chart

**Resolution:** `zxReleaseElement` gets `getEffectiveHardwareCodes(): string[]` returning its own
codes ∪ its prod's, and every consumer above uses that instead of the raw `hardwareRequired`
property. The raw property keeps meaning "this release's own deviations" — it is what the edit form
binds to, what `publicReceive` writes, and what the release card and hero display. Only the
behavioural consumers see the union.

Rules for the implementation:
- The union lives in `ProdHardwareService` (D.1), reached from the release through `getProd()`,
  which is already memoized on the element (`$prodElement`, :369-393). It must be lazily computed
  and cached per element instance — `isPlayable()`, `getEmulatorType()`, `getPlayableFiles()` and
  `getListImagePreset()` can all be called within one request for the same release.
- `getProd()` returns `null` for an orphaned release, so the method must fall back to the release's
  own codes rather than failing.
- `StatsRepository` cannot go through elements (it is a pure aggregate query), so it needs the SQL
  equivalent: union the two link tables via `structure_links`, same subquery as the D.5 filter.

This turns the largest regression surface of Task 2 into a single method, and it also means the
D.4 fix script can be re-run or partially applied without ever leaving a release unplayable
mid-migration.

Per-row SPA changes:

| # | Change |
|---|---|
| 1 | `zx-prod-block` badges keep showing the **effective** set so cards do not go blank for unmigrated prods; `ProdsTransformer::buildHardwareInfo()` → `getAggregatedHardwareCodes()` |
| 2, 3 | release card / row: shape unchanged, now showing only deviations. Many shrink to a few codes, some to none — the existing `*ngIf` guards must render the empty cell/section cleanly |
| 5 | `zx-release-hero`: renders **both** sets — inherited prod chips neutral, release-specific chips `color="primary"`; needs the new `prodHardware` field (D.2) |
| 6-10 | catalogue selector and the click-to-filter badge flow must count prod-level rows too (D.5) |
| 11 | release form: same field, now the shared `zx-hardware-select` |
| — | **new**: prod form field and prod hero chip row (D.2) |
| 12 | stats: see above |
| 13-15 | icons and list DTOs: shape unchanged |

Legacy templates/presets also moving to the effective set: `zxProd.class.php` (63-67, 146-150),
both `getLdJsonScriptData()` implementations, `templates/simple/zxRelease.details.tpl:33-40`.

### D.4 One-off fix script (Task 2.3)
Per prod, as specified:
1. Load the prod's releases; skip those with no hardware.
2. If the prod has **≥1 `original` release** → the source set is the original release(s) **only**.
   Otherwise → all releases with hardware.
3. The prod's set = the **intersection** of the source releases' sets.
4. Write it to the prod.
5. For **every** release of the prod, remove the codes now on the prod.

Implementation:
- The algorithm goes into a testable `ZxArt\Prods\Services\ProdHardwareMigrationService`; the
  `fix.class.php` method only loops and calls it (and needs the parameter dispatch of C.8).
- Idempotent: skip prods that already have their own hardware unless `?force=1`.
- `?dry=1`, resumable `?offset=`, batched, `echo` progress — 53 426 prods will not fit in one request.
- Clear the element and `hardwareInfo` caches for every touched prod and release.
- Run **after** the Task 0 backfill (C.8), so derived codes take part in the intersection.
**Several `original` releases → intersect them.** The task says *"…ТО ЖЕЛЕЗО СОБИРАЕМ ТОЛЬКО С
НЕГО"*, singular, but **10 184 prods have more than one `original` release with hardware** (of
36 046 that have any), so the script needs an explicit rule. It is the **intersection of all
originals** — the literal reading of "наименьший общий набор":

```
release A (original):  zx48,  ay, kempston
release B (original):  zx128, ay, kempston
                ↓
prod        →  ay, kempston
release A   →  zx48
release B   →  zx128
```
The prod claims only what is true of every original; the machine difference stays on the releases,
where it is real. Union would make the prod claim it needs both machines at once; "earliest
original" would turn a co-equal release into a port.

**Empty intersection → leave the prod empty.** For **1 133** of those 10 184 prods the originals
share no code at all (a tape release for a 48K and a disk release for a Pentagon, with different
sound and controls). There is no union fallback: the prod keeps no hardware of its own, everything
stays on the releases, and the prod hero simply renders no chip row. Nothing false is written, and
the prod is not invisible in the catalogue anyway — cards and the `hw` filter both go through
`getAggregatedHardwareCodes()` / the union subquery (D.3, D.5), so such a prod still shows and
still matches on the union of its releases' codes.

Consequences for the implementation:
- The prod hero (D.2) must render nothing at all — not an empty row or a heading — when the prod's
  own set is empty. Same for the release hero's inherited-chip group.
- These 1 133 prods are the natural spot-check set for the `?dry=1` run.

### D.5 Filter API (Task 2.4)
The `hw` catalogue filter must match "the prod's own hardware **OR** any of its releases'".
`QueryFiltersManager` cannot OR two filters (A.6), so:

- **New** `project/modules/queryFilters/zxProdHardware.class.php` — required type `zxProd`, one
  `whereIn('module_zxprod.id', …)` whose subquery unions both sources: prod ids from
  `module_zxprod_hw_required`, plus prod ids reached from `module_zxrelease_hw_required` →
  `structure_links` (type `structure`) → parent prod. Query Builder only.
- **New** `project/modules/queryFilters/zxReleaseEffectiveHardware.class.php` — required type
  `zxRelease`, matching "the release's own hardware **OR** its prod's". This is what the catalogue's
  **release mode** uses. `ZxProdsList::getProds()` (:76-78) switches to `setResultTypes(['zxRelease'])`
  when `getReleasesValue()` is true, reusing the same `$filters` array, so two things must hold:
  the prod-typed `zxProdHardware` must **not** be used there (it would convert zxProd→zxRelease and
  return *every* release of every matching prod instead of only the matching ones), and a plain
  own-codes-only filter would drop every release whose codes moved to its prod. So `getFilters()`
  picks per mode: `zxProdHardware` in prod mode, `zxReleaseEffectiveHardware` in release mode.
- **The public site stays exact; the documented `/api/` filter is allowed to narrow.** Both
  catalogue modes above match on the effective sets, so nothing the site renders changes. The
  external filter **`zxReleaseHardware` keeps its literal meaning** — "releases carrying this code
  themselves" — and therefore matches fewer releases after D.4, which is accepted. Two follow-ups:
  note the narrowed semantics on the API page (`api.{en,ru,es}.html` ~line 618) rather than leaving
  third parties to discover it, and document `zxProdHardware` there as the filter that answers
  "software that runs on X".
- Filter arguments arrive as **codes**. Translate them to ids once via
  `HardwareCatalogService::getIdsByCodes()` — a cached 119-row lookup — so the big query compares
  integers and the `hardwareId` index stays usable. (Joining `engine_hardware` inside the subquery is
  equivalent and can be used instead if you prefer keeping the translation in SQL.)
- `ZxProdsList::getHardwareSelector()` must count from **both** tables, or a code that now exists only
  on prods disappears from the filter list. Same union subquery, in one repository method shared with
  the filter.
- `zxProdElement::getHardware()` loses its `convertTypeData` round-trip for the own-set case and
  becomes a plain indexed read — cheaper than today.
- Re-check the "no filters set" / "reset" conditions at `ZxProdsList.php:727, 804, 825`.

### D.6 Tests
`ProdHardwareServiceTest` (aggregated vs effective set, both directions), filter tests for
`zxProdHardware` and `zxReleaseEffectiveHardware` on a seeded fixture covering both catalogue modes,
unit tests for the intersection/subtraction algorithm of D.4 including the empty-intersection
fallback, a regression test that a release whose hardware moved to its prod is still playable (D.3),
a test that `prodHardware` excludes codes the release already lists, and a batch-upload test
asserting hardware lands on the created prod and not on its release.

### D.7 Imports — audit and rerouting

Every importer funnels its hardware through **one** line:
`ProdsService::updateRelease()` — `if (!$element->hardwareRequired && !empty($dto->hardwareRequired))`
(ProdsService.php:776-777). `ReleaseImportDTO` carries `hardwareRequired`;
**`ProdImportDTO` has no such field and `updateProd()` has no hardware handling at all.**

Where each source's hardware actually belongs:

| importer | where its hardware comes from | decision |
|---|---|---|
| **`ZxdbImport`** | `minMachines`/`optionalMachines` keyed by `machinetype_id`, which **falls back from the download to the entry** (:727-729) and again at :761-762. Plus `featureGroups[tag_id]` from the `members` table queried **by `entry_id` only, with no `release_seq` filter** (:815-824), so every release of an entry already gets an identical control set. | **→ prod, with no per-release distribution at all.** The entry *is* the prod and ZxDB is original software. |
| **`WorldOfSamImport`** | the constant `['samcoupe']` (:492), applied to every release it creates (:518, :542) | **→ prod** |
| **`VtrdosImport`** | parsed per file from titles, anchors and category (`categoryHardware` :66, `VtrdosHardwareProvider::match()` :829) — vtrdos entries describe individual files and one prod's files genuinely differ | **→ release** (unchanged) |
| **`ZxaaaManager`** | derived from the file extension via `$extWords` (:62, :337-338) | **→ release** (unchanged) |
| **`PouetImport`** | `$prodData['platforms']` through the `$platforms` map (:101-106, :618-626) | **→ dropped entirely.** The source is not trustworthy. |

Changes:

1. `ProdImportDTO` gains `public ?array $hardwareRequired = null` and the matching `fromArray()` line.
2. `ProdsService::updateProd()` gains the mirror of the release guard —
   `if (!$element->hardwareRequired && !empty($dto->hardwareRequired)) { … }` — so an import never
   overwrites a value an editor set by hand.
3. **`ZxdbImport`** fills `ProdImportDTO::$hardwareRequired` and stops filling
   `ReleaseImportDTO::$hardwareRequired` completely — the entry machine type, the optional machine
   and the `members` control set all go to the prod. The per-download `machinetype_id` branch
   (:727-733) collapses into the entry-level one; no ZxDB code lands on a release any more.
4. **`WorldOfSamImport`** moves its `['samcoupe']` constant to `ProdImportDTO`.
5. **`PouetImport`** loses hardware entirely: delete the `$platforms` map (:101-106), the loop at
   :618-626 and the `hardwareRequired:` argument at :639. Why it is untrustworthy is visible in the
   map itself — `'ZX Spectrum' => 'zx48'` asserts "runs on a 48K" for every pouet prod tagged with
   the generic platform, which is wrong for most demos, and it is the source of **2 173** `zx48`
   rows on pouet-imported elements. (`'ZX Enhanced' => ''` would also append an empty code; it is
   harmless today only because `DBValueSetDataChunk::persistExtraData()` drops falsy values, which
   is why there are 0 rows with `value = ''`. Deleting the map removes that latent bug too.)
6. **`VtrdosImport`** and **`ZxaaaManager`** keep writing release-level codes and are otherwise left
   alone. Note `ZxaaaManager`'s extension-derived logic overlaps `ReleaseHardwareAutofillService`
   (C.2); reconciling the two is worth doing but is not required by this plan.

**Existing pouet data is not purged.** Hardware rows carry no provenance, so a row cannot be
attributed to the importer after the fact, and the pouet-imported elements also hold codes the
`$platforms` map cannot produce (`zx128` 120, `tsconf` 41, `pentagon128` 15, `ay` 19, …) — those are
editor-curated. Deleting by origin would destroy them. The importer simply stops adding new ones;
cleaning the historical `zx48` rows, if wanted, is a separate curated decision.

**Import de-duplication breaks unless it is updated too — this is the sharp edge.**
`ProdResolver:72` gates "is this imported prod the same as that existing one?" on
`HardwareCompatibilityService::areProdAndDtoCompatible()`, which compares **release-level** hardware
on both sides and contains:

```php
if ($dtoHasHardware && !$prodHasHardware) { return false; }
```

After D.4 strips shared codes off existing releases, `$prodHasHardware` becomes false for a large
share of prods, so an incoming DTO that has hardware stops matching its existing prod and
**ProdResolver creates a duplicate prod on every subsequent import run**.

**Both sides of the comparison have to move**, and the DTO side is not optional either: once ZxDB
and World of Sam write only `ProdImportDTO::$hardwareRequired`, `hasHardware($dto->releases)`
returns false for them, which sends them down the permissive
`if (!$dtoHasHardware && $prodHasHardware) { return true; }` branch — matching would get *looser*,
merging prods that should stay apart. So `areProdAndDtoCompatible()` must compare:

- prod side: `$prod->getAggregatedHardwareCodes()`
- DTO side: `$dto->hardwareRequired` ∪ the codes of all `$dto->releases`

as two flat sets through `HardwareCompatibilityRules::codesToGroups()`, instead of the current
release-by-release loop. Pouet, now carrying no hardware at all, keeps the permissive branch, which
is the intended behaviour for a source whose platform data is not trusted.

Cover it with two tests: a prod migrated by D.4 still matches its own re-import, and a ZxDB DTO with
prod-level hardware does not match a prod of a different machine family.

Ordering: this change must ship **with** D.4, not after it — the window between the migration and
the fix is exactly when duplicates get created.

---

## Part E — Cross-cutting risks

- **The duplicated ENUM value (A.2)** blocks every form of DDL on the release link table, including
  `mysqldump` reload. B.1 step 0 is not optional and must be the first statement.
- **D.3 was the biggest regression surface** — five backend consumers read the release's own
  hardware and would lose data silently after the Task 2 migration, online emulation most visibly.
  It is resolved by `zxReleaseElement::getEffectiveHardwareCodes()`; the remaining risk is a
  consumer added later that reaches for the raw property out of habit, so the raw
  `hardwareRequired` deserves a docblock saying what it now means.
- **`HardwareCompatibilityRules::ITEM_TO_GROUP`** contains a dead `zx81` entry. Task 0 leans on this
  map, so remove it (the individual `zx811`/`zx8116`/… codes are already listed).
- **`zxProdElement::getHardwareInfo()`'s cache key omits `$short`** (A.7) — a pre-existing bug that
  the prod-hardware work will otherwise entrench.
- **MyISAM → InnoDB** changes locking behaviour; it is a 101 702-row table so the conversion is quick,
  but it belongs in a maintenance window with the B.1 step-3 gate checked before `DROP COLUMN`.
- **Import de-duplication (D.7)** silently creates duplicate prods once D.4 strips release hardware,
  because `HardwareCompatibilityService` compares release-level sets. It must ship together with the
  migration, not after it. This is the only issue in the plan whose damage accumulates unnoticed.
- **`ZxaaaManager`** already auto-derives hardware from extensions; unreconciled, it will fight the
  new service (D.7).
- **Privileges are session-cached**, so a SQL grant needs a re-login to take effect.

---

## Part F — Decisions

All confirmed with the task author during review; the sections named in brackets carry the detail.

1. **Auto-fill writes to the release** (Part C). Format is a property of the release file, and Task 2
   defines release-level hardware as the release-specific adaptations — exactly what a storage/DOS
   code derived from a file format is.
2. **Ambiguous formats are resolved smartly, or not at all** (C.4-C.6): format × machine family
   first, sibling releases as fallback, "add nothing" as the default; the unambiguous disk system is
   added on its own where genuinely determinable. Missing codes get added (B.2).
3. **Link tables store `hardwareId`** (B.1, B.3): a dynamic catalog rules out an ENUM, and an indexed
   int beats a `VARCHAR` in every filter query. Codes stay the contract above the database.
4. **`name` + `shortName` per language** (B.1): 40 of 120 codes have a genuinely different short
   form, and every card, badge and release-table row depends on it.
5. **Categories are an enum column, with no management form** (B.1, B.9). Task 1.5 wins over the
   "category на трёх языках" reading of 1.1: `category` is a hardcoded `ENUM` on `engine_hardware`,
   its names stay SPA-owned (`hardware-group.*`), and the management section edits hardware items
   only. No third table, no category screen.
6. **`zxReleaseHardware` may match fewer releases** (D.5) — the public site must stay correct, the
   documented `/api/` filter may narrow. See D.5 for how the two are separated.
7. **The release page shows the prod's hardware too** (D.2), with chip colour separating inherited
   from release-specific.
8. **The batch upload form gets a hardware field, applied to the prod only** (D.2).
9. **Several `original` releases → intersect them; empty intersection → leave the prod empty**
   (D.4). No union fallback: nothing false gets written, and such prods still appear in the
   catalogue and still match the `hw` filter through the aggregated set.

Nothing is left open. Every point of hardware.md has a decided answer.
