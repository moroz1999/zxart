# Hardware — Implementation Checklist

Progress tracker for [hardware-plan.md](hardware-plan.md). Section ids (B.1, C.3, D.7 …) refer to
that plan. `[x]` done and verified, `[~]` in progress, `[ ]` not started.

---

## Task 1 — hardware catalog table + management section

### B.1 Migration
- [x] `db/migrations/2026.08.08 - hardware catalog.sql` written
- [x] Step 0 — `MODIFY value VARCHAR(32)` to clear the duplicate-`aymouse` ENUM block
- [x] Step 1 — purge 3 638 orphan rows and 206 duplicate `(elementId, value)` pairs
- [x] Step 2 — `engine_hardware` + `engine_hardware_name` created, 122 codes / 366 name rows seeded
      (names keyed by ISO 639-1 `languageCode`, not by CMS language id)
- [x] Step 3 — link table → InnoDB, `hardwareId` column, gate, `value` dropped, FK added
- [x] Step 4 — `root:editHardware` granted to `catalogues-managers` on the public root
- [x] Applied to the local DB: 101 702 → 97 858 rows, gate returned 0, delta = 3 638 + 206 exactly

### B.2 Codes added to the catalog
- [x] `zxevolution` (had live rows, no `HardwareItem` case)
- [x] `defender` (was in the enum but unstorable)
- [x] `samdos`, `gdos`, `mb02` (new, hand-written names)
- [x] `covox`, `hdd`, `alf1` deliberately not seeded

### B.3 `DBValueSet` chunk — ids in storage, codes in the element
- [x] Optional `lookupTable` / `lookupIdField` / `lookupCodeField` config on `DBValueSetDataChunk`
- [x] `loadStorageValue()` maps stored ids → codes
- [x] `persistExtraData()` maps codes → ids **before** the diff loop, drops unknown codes
- [x] Map cached statically per lookup table name (not per chunk instance)
- [x] `zxRelease.hardwareRequired` declaration switched to the lookup config
- [x] Verified live: release 92671 still reports `['zx128','ay','kempston','int2_2']`

### B.3a Call sites of the dropped `value` column *(not in the plan — see Deviations)*
- [x] `queryFilters/zxReleaseHardware` — resolves codes → ids via the catalog
- [x] `ZxProdsList::getHardwareSelector()` — joins the catalog for codes
- [x] `zxProdElement::getHardware()` — joins the catalog for codes
- [x] `StatsRepository` hardware distribution — joins the catalog for codes
- [x] Verified live: catalogue 20 476 prods unfiltered, 12 742 `hw=zx48`, 218 `hw=samcoupe`,
      12 959 for both, empty for an unknown code

### B.4 Backend catalog service
- [x] `DatabaseTable::Hardware`, `DatabaseTable::HardwareName`
- [x] `Dto/HardwareItemDto`, `Dto/HardwareNameDto`
- [x] Request DTOs: `Dto/HardwareSaveDto`, `Dto/HardwareNameInputDto`, `Dto/HardwareDeleteDto`
- [x] `Shared/Serializer/RequestDenormalizerFactory` + `DenormalizerInterface` in DI
- [x] `Rest/HardwareItemRestDto`, `Rest/HardwareNameRestDto` (+ `#[Map]`)
- [x] `Exception/HardwareException`
- [x] `Repositories/HardwareRepository`
- [x] `HardwareCatalogService`
- [x] `HardwareCatalog` delegates to it (`HardwareProvider` reaches it through `HardwareCatalog`)
- [x] `HardwareCompatibilityRules` — dead `zx81` entry dropped
- [x] `Shared/ObjectMapper/BackedEnumValue` transform *(not in the plan — see Deviations)*
- [x] `Shared/InterfaceLanguage` enum (`en`/`ru`/`es`, bridges the CMS's iso6393)

### B.5 Naming pipeline flip
- [x] `ProdHardwareInfoDto` / `ProdHardwareInfoRestDto` → `{id, name, shortName, category}`
- [x] `ProdInfoBuilder::buildHardwareFromCodes()` is the single builder, labels from the service
- [x] `ProdsTransformer::buildHardwareInfo()`
- [x] `HardwareProvider::getHardwareDetails()` — the shared 4-key shape for the legacy converters,
      so `/prodlist/` and the catalogue emit the same contract *(not in the plan — see Deviations)*
- [x] `ZxProdsList::getHardwareSelector()` sends real titles
- [x] `Formdata::enumSpecs()` — new `options` mode + `getHardwareOptions()`, labelled **and** grouped
- [x] Legacy label consumers → `HardwareCatalogService::getLabels()`
      (`zxRelease`/`zxProd` `getHardwareInfo()`, both `getLdJsonScriptData()`,
      `templates/simple/zxRelease.details.tpl`)
- [x] Stats computer-model distribution labels resolved server-side *(not in the plan)*
- [x] Angular DTOs widened (`ProdHardwareInfoDto`, `ZxProdHardwareItem`, `EnumOption.group`,
      `prods-browser.service`)
- [x] Angular components read names from the response (prod block, release card, release row,
      release hero, catalogue selector, release form, stats)
- [x] `hardware.*` / `hardware-short.*` removed from `en/ru/es.json` (735 lines), `hardware-group.*` kept
- [x] `shared/utils/hardware-groups.ts` — shared grouping helper for every hardware form
- [x] Verified live: catalogue selector shows "ZX Spectrum 48K", release hero/rows carry
      `name`/`shortName`/`category`, stats shows short names, `X-Language` switches them
      (`tape` → Tape / Магнитная лента / Cinta)

### B.6 Icons
- [x] `zx-hardware-icon` takes `category` and falls back per category before the generic default
      *(the plan said no category fallback; revisited — see Deviations)*
- [x] Explicit entries for `samdos`, `gdos` (`hw-os`) and `mb02` (`hw-disk`); `defender` already had
      one. `zxevolution` deliberately has none — no computer code does, they all use the default.

### B.7 Privilege
- [x] Granted in the migration (step 4) to `catalogues-managers`; `content_managers` intentionally
      not granted
- [x] `editHardware` added to the `root` module action list
- [x] `publicRootId` on `CurrentUserRestDto` (verified: `/currentuser/` returns 1)

### B.8 Backend controller
- [x] `ZxArt\Controllers\HardwareData` at `/hardware-data/`
- [x] Privilege gate on writes, open `GET`
- [x] `create` / `update` / `delete` with validation and the in-use delete guard
- [x] Request bodies denormalized into `HardwareSaveDto` / `HardwareDeleteDto` by
      `symfony/serializer` — no array shapes below the controller
- [x] Verified live: `GET /hardware-data/` returns 122 items, 3 languages, usage counts;
      an unauthenticated write is 403 before the body is even parsed
- [x] Write actions exercised over HTTP with a real session: create → 123 items, update changes
      code/category/names, duplicate code → 409, delete of an in-use item → 409, delete of an
      unused one → back to 122
- [x] `SpaRouter` pattern for `/manage` (verified: `/manage` serves the SPA, `/admin` still legacy)
- [x] OpenAPI: new `api/hardware-data.yaml`; `ProdHardwareInfo` widened in `prod-details.yaml`
      (inherited by `prod-releases.yaml` and `release-details.yaml` through their `$ref`);
      `author-details.yaml` hardwareInfo re-pointed from `ConnectedItem` — it documented the wrong
      shape; `current-user.yaml` gained `publicRootId`; `form-data.yaml` describes the new `group`
      on enum options. All 40 specs parse.

### B.9 Frontend management section
- [x] `rootPrivilegeGuard` + `RootPrivilegeService` (asks once per session, replays)
- [x] `publicRootId` carried through `CurrentUser` so no id is hardcoded
- [x] Routes `/manage` → `/manage/hardware`, `/manage/hardware/add`, `/manage/hardware/:id`
- [x] `pages/manage-hardware/` (list grouped by category, names per language, usage counts)
- [x] `pages/manage-hardware-edit/` (create/edit/delete, all languages at once)
- [x] `features/manage-hardware/` service + models
- [x] Menu entry in the user popover, gated on the privilege
- [x] i18n keys in `en/ru/es` (`manage-hardware.*`, `menu.manage`)
- [x] `composer run build` clean
- [x] Verified: all four routes serve the SPA (the router pattern needed widening to two
      segments — one-segment pattern 301'd `/manage/hardware/add`)
- [ ] `pages/manage/` tabs shell — deferred until a second management section exists

### B.10 Tests
- [x] `HardwareCatalogServiceTest` (11 tests: grouping, label fallback, create/update/delete,
      code normalization, per-language completeness, in-use delete guard)
- [x] `HardwareSaveDtoTest` (7 tests: denormalization through the real factory, defaults, rejections)
- [x] `HardwareRepositoryTest` (5 tests: row typing and usage aggregation)
- [x] Chunk code↔id mapping both directions, unknown codes, diff ordering
      (`tests/Hardware/DBValueSetLookupTest.php`, 5 tests)
- [x] Controller privilege gate — verified live rather than unit-tested: anonymous write → 403
      before the body is parsed, privileged session → full CRUD. The project has no controller-test
      harness and `LoggedControllerApplication::__construct` does DI work, so a unit test would test
      the mocks, not the gate.
- [x] `SpaRouterTest` for `/manage` (+ `/admin` asserted non-SPA)
- [x] `defender` is storable and renders: catalog id 111, written to a release and read back
      through `/release-details/` as "Defender Light Gun". The ENUM made this impossible before.
- [x] Full suite green after the changes so far (363 tests)
- [x] `composer run build` clean

---

## Task 0 — auto-fill from release format

- [x] `ReleaseHardwareAutofillService` — stage 1 format rules, stage 2 format × machine family,
      stage 3 sibling inference, DOS guard, catalog validation
- [x] Hooked into `zxReleaseElement::updateFileStructure()` via `autofillHardware()`, with
      `getHardwareAutofillAdditions()` so the backfill previews and applies the same result
- [x] `ReleaseHardwareAutofillServiceTest` — 49 tests: every stage-1 rule, every stage-2 row, the
      no-rule formats, both DOS guards, sibling inference (one / none / several families), its
      refusal to add a computer code, idempotency, empty input, and a code missing from the catalog
- [x] Backfill job `/fix/job:hardware-autofill/` with `dry:1`, `offset:N`, `limit:N`; `fix.class.php`
      gained the `?job=` dispatch it lacked
- [x] Verified live: dry run reports `tzx → +tape` and `dsk`+`zx128+3` → `+3dosdisk, 3dos`;
      a real run wrote it; a second run changed nothing
- [x] Backfill exercised on a real batch (~2 000 releases) and the result checked: `tape` 432 → 2 175,
      `3dosdisk` +98 exactly matching `3dos` +98, `betadisk` +7 exactly matching `trdos` +7 — the
      pairs the rules emit together. No computer code was added anywhere. 13 releases site-wide carry
      two DOS codes, all editor-set combinations the guard cannot produce (`trdos+trdos4x`,
      `3dos+trdos`, `isdos+tasis`, `cpm+tos`).
- [ ] Production run — batched, by the site owner. The local copy is deliberately left partial.
- [x] `ZxaaaManager` needs no reconciliation — checked the source: it derives machine and sound
      codes from the title and from a sound-chip listing column, never from a file format. The
      plan's claim that its `$extWords` mapped file extensions was wrong (the name misled me) and
      has been corrected.

- [x] **Fixed a pre-existing data-loss bug found while testing:** saving a release whose file was
      missing from disk wiped its `releaseFormat`. The no-file branch now distinguishes "no file
      reference" (clear, as before) from "file unreadable" (keep structure and formats, still run
      auto-fill, set `parsed`). Software on sale legitimately has no downloadable file. Plan C.2.

## Task 2 — hardware on the prod

### D.1 Storage + element
- [x] `db/migrations/2026.08.09 - prod hardware.sql`, applied locally
- [x] `hardwareRequired` on `zxProdElement` (same id-based `DBValueSet` config as the release)
- [x] `DatabaseTable::ZxProdHardware`
- [x] `ProdHardwareRepository` (aggregated codes, inherited codes, both filter shapes, selector ids)
- [x] `ProdHardwareService` — memoized per request
- [x] Prod: `getHardware()`/`getHardwareCodes()` are now its **own** codes;
      `getAggregatedHardwareCodes()` looks down at its releases

### D.3 Release resolves its prod's hardware
- [x] `zxReleaseElement::getEffectiveHardwareCodes()` (own ∪ prod's)
- [x] `EmulatorResolverService`, `ArchiveFileResolverService` and the release LD-JSON use it
- [x] `ZxSoft::getListImagePreset()` asks the new `getRunsOnHardwareCodes()` — aggregated on a prod,
      effective on a release — instead of reading raw codes *(interface method added; not in the plan)*
- [x] Stats computer-model distribution counts both sources, merged on prod id so a prod carrying a
      code in both places is not counted twice *(the plan only said "switch it"; the join could not
      express the union, so it reads two sources and merges — 36 447 pairs, cheap enough)*
- [x] Display split: card = runs-on set, form and detail = own codes

### D.2 Form, action, batch
- [x] `hardwareRequired` in the prod's `publicReceive` expected fields
- [x] `Formdata::enumSpecs()` for `zxProd` and `zxProdsUploadForm`
- [x] Batch upload form field, applied to the created production only
- [x] Prod edit page field (grouped picker, always submitted so `expectedFields` cannot wipe it)
- [x] Prod hero chip row — the prod page showed no hardware at all before
- [x] Release hero shows both sets: inherited chips neutral, release-specific `color="primary"`
      with a tooltip; `prodHardware` excludes anything the release repeats
- [x] i18n for both new labels, `composer run build` clean

### D.5 Filters
- [x] `zxProdHardware` (prod's own OR its releases')
- [x] `zxReleaseEffectiveHardware` (release's own OR its prod's)
- [x] `ZxProdsList` picks per mode; the hardware selector reads both sources
- [x] Verified: prod mode 20 476 / 12 742 zx48 / 218 samcoupe / 12 959 both; release mode works
- [x] `zxReleaseHardware` semantics documented on the API page in all three languages, with
      `zxProdHardware` added next to it as the filter that answers "software that runs on X"

### D.4 + D.7 — must ship together
- [x] `ProdHardwareMigrationService` (originals → intersection; empty intersection leaves the prod empty)
- [x] `/fix/job:prod-hardware-migrate/` with `dry:1`, `force:1`, `offset:N`, `limit:N`
- [x] Dry run verified on real data
- [x] `ProdImportDTO::$hardwareRequired` + `ProdsService::updateProd()` guard
- [x] ZxDB → prod only (entry machine type + control tags; nothing per release)
- [x] World of Sam → prod
- [x] Pouet → hardware dropped entirely
- [x] `HardwareCompatibilityService` compares flat sets (prod aggregated vs DTO own ∪ releases')
- [x] Verified end to end on a real migrated production: prod 92676 keeps the shared controls,
      release 92679 keeps `zx128, tape`, release 328342 keeps nothing and inherits all three
- [x] **The regression D.3 exists for**: release 92686 was emptied by the migration and is still
      playable (`emulatorType: usp`, playUrl set) because the machine now resolves from its prod
- [x] Catalogue still finds prods by a code that now lives only on the production
- [ ] Full migration run — production, by the site owner, only after D.7 is deployed

### D.6 Tests
- [x] `HardwareCompatibilityServiceTest` extended: prod-level DTO hardware, a migrated prod matching
      its own re-import, a different machine still rejected
- [x] `ProdHardwareServiceTest` (7 tests: both directions, de-duplication, memoization, unsaved elements)
- [x] `ProdHardwareMigrationServiceTest` (16 tests: intersection from the originals, per-category
      subtraction, the stated-nothing-lost invariant, releases without
      hardware, the empty-intersection skip, single release, no releases)
- [x] `HardwareQueryFiltersTest` (4 tests: code→id resolution, correct column per type, unknown
      codes dropped, and that the public API filter stays own-codes-only)

---

## Deviations from the plan
Recorded as they happen, so the plan and the code do not drift apart silently.

- **B.3a — dropping the `value` column breaks four existing queries, which the plan did not list.**
  `queryFilters/zxReleaseHardware`, `ZxProdsList::getHardwareSelector()`,
  `zxProdElement::getHardware()` and `StatsRepository` all read
  `module_zxrelease_hw_required.value` directly, so they had to be converted in the same step as
  the migration rather than later in B.5/D.5. They now join `engine_hardware` for the code, except
  the filter, which resolves codes → ids through the cached catalog (no join in the hot query, as
  D.5 prescribes).
- **`Shared/ObjectMapper/BackedEnumValue` added.** Mapping `HardwareGroup` to its string needed a
  transform. Two ObjectMapper behaviours worth knowing, both discovered here:
  1. property-level `#[Map]` is read from the class carrying the **class-level** `#[Map]` (the
     internal DTO), not from the REST DTO;
  2. a transform passed as a class-string (`MapCollection::class`) is **silently ignored** — no
     callable locator is configured — so it must be an instance (`new MapCollection()`).
  Consequence worth a separate look: the existing `#[Map(transform: MapCollection::class)]`
  attributes on `ProdReleaseRestDto`, `ProdCoreRestDto` and friends are therefore no-ops today.
  Harmless so far only because those nested internal and REST DTOs happen to serialize identically.
- **`HardwareProvider` was left untouched.** It calls `HardwareCatalog`, which now delegates to the
  service, so the trait needed no change.
- **`HardwareItemDto::$usages`** was added (not in the plan): the management list shows the count
  and the delete guard needs it.
- **`symfony/serializer` added as a dependency** (requested during implementation) so request
  bodies are denormalized into typed DTOs instead of being hand-parsed. Only `symfony/object-mapper`
  was installed before, and it cannot do this: it copies values by property name without converting
  types, so a string never becomes a backed enum. Now the project convention — written up in
  [docs/php/rest-api.md](../php/rest-api.md). Existing controllers still hand-read arrays and are a
  separate migration.
- **The legacy `list` presets and `/prodlist/` had to converge on one shape.** The catalogue page
  reads hardware from the legacy `zxProdsList` preset while `/prodlist/` goes through
  `ProdsTransformer`; after widening only one of them the same Angular field would have carried two
  different shapes. `HardwareProvider::getHardwareDetails()` is now the single source for both.
- **`zx-hardware-icon` did get a category fallback**, contrary to B.6. The plan argued a category
  rule would contradict the explicit map (DOS codes deliberately point at `hw-disk`). It does not:
  the explicit map is still consulted first and always wins, and the category only decides what an
  *unmapped* code gets — which now matters, because codes are editable. All six category icons
  already exist in `assets/svg/`.
- **`zx-hardware-select` was dropped in favour of `shared/utils/hardware-groups.ts`.** The plan
  called for a shared `ControlValueAccessor` component, but it would wrap `zx-multi-select-filter`,
  which is itself a CVA — the nesting needs `ngModel` plumbing and cannot forward the disabled
  state. The only genuinely shared part is building the groups, so that is a plain function the
  forms call; each form keeps `formControlName` on `zx-multi-select-filter` directly.
- **Stats labels moved server-side too** (not in the plan's B.5 list): the computer-model chart
  used the `hardware-short` translations that B.5 deletes, so `StatsService::buildDistribution()`
  gained an optional label override fed from the catalog.
- **Site-wide privileges only resolve for users who may `show` the public root.**
  `ElementPrivilegesService` loads the element first, and `structureManager` refuses to hand back an
  element the user cannot `show` — so `/element-privileges/?id=<root>` answers 404, not `false`, for
  a user outside `public_visitors`. Every real account is in that group (it carries `root:show` on
  element 1), so this is not a live problem, but it makes the group a hidden prerequisite of the
  management section and it is what made a synthetic test user look like a broken privilege.
- **`engine_hardware_name` is keyed by a two-letter language code, not the CMS language id**
  (requested during implementation). `ZxArt\Shared\InterfaceLanguage` (`en`/`ru`/`es`) is the enum;
  it matches the SPA i18n filenames and the REST payloads. The CMS works in iso6393 internally, so
  `HardwareCatalogService::getCurrentLanguage()` reads `module_language.iso6391` and
  `InterfaceLanguage::fromIso6393()` bridges the other direction. The plan's B.1 was updated to
  match; the migration was rewritten in place because it has not been deployed.
- **Stage 3 (inferring the machine from the production's other releases) was dropped, and autofill
  now compares against the effective set** (decided during implementation). The plan had
  `getHardwareAutofillAdditions()` gather the sibling releases on every release save — expensive
  (a load of the production and all of its releases) and, as measured, largely redundant: once
  Task 2 moves the shared hardware onto the production, 85 of the 89 releases the stage settled get
  their machine family straight from the prod via `getEffectiveHardwareCodes()`, with no sibling
  scan at all. `getSiblingHardwareCodes()` and the whole third stage are gone; the effective set now
  supplies both the machine and the definition of "already present". The 4 releases the prod cannot
  cover have `original` releases that disagree on the machine or name none, so the intersection the
  migration takes holds no computer — accepted.

  **Consequence: the one-off jobs are now ordered.** `prod-hardware-migrate` must run before
  `hardware-autofill`, otherwise unmigrated productions give stage 2 nothing and the backfill has to
  be repeated. Recorded in `docs/domain/hardware.md`.

- **Autofill compares against the effective set, not the release's own** — the same change also
  fixed a Task 0 / Task 2 interaction the plan did not anticipate: once the migration moved a
  production's shared codes up, every later save of one of its releases proposed adding them
  straight back. Release 92686 (`tzx`, own set emptied by the migration, `tape` inherited from prod
  92683) reproduced it. Additions are still written to the release, since a code that is new there
  is genuinely release-specific.
- **The element cache must be dropped as part of the deploy** (found on the production rollout).
  The cache holds serialized structure elements including their data chunks, so cached entries keep
  the old `hardwareRequired` configuration no matter how new the deployed code is: releases still
  ask for the dropped `value` column, and productions have no `hardwareRequired` chunk at all, so
  `getHardwareCodes()` gets null out of `__get()`. Both look like a partial deploy and are not.
  Added as an explicit step in `docs/domain/hardware.md`.
- **The shared set is subtracted per category, not as a whole** (found on the production rollout —
  the live database had to be restored from backup). Part F specified sourcing the shared set from
  the `original` releases, which is right; what was wrong was taking the whole set off every
  release. With originals carrying `zx48, zx128, ay` and a 128K-only re-release carrying
  `zx128, ay`, the re-release had both subtracted as "already said", kept nothing, and inherited
  `zx48` on top: a release that meant one machine ended up meaning two. Not limited to machines — a
  crack carrying only `zx48` under an original carrying `zx48, ay, kempston` was hit the same way.
  `subtract()` now removes a category only when the release states it exactly as the production
  does; anything else stays whole, because a release cannot inherit back a category it speaks
  about. `testNoReleaseLosesOrChangesWhatItStated` pins the invariant over five production shapes
  and fails under the old whole-set subtraction.

  My first fix was to widen the sourcing to every release with hardware. That also stopped the data
  loss but was the wrong diagnosis, and it threw away the editorial point of the `original` rule —
  reverted once the per-category subtraction was in.

- **The union was considered as an alternative and rejected.** Giving the production the union of
  its releases' machines would let the 6 productions that end up without one get a machine, but it
  breaks the same way round: a production holding a 48K-only release, a 128K-only release and a
  universal one would claim `zx48, zx128`, and both single-machine releases would read as
  supporting each. The intersection stands and such productions stay without a machine.
- **Inheritance is gap-filling per category, not a union** (decided after the production incident).
  `own ∪ prod` cannot express a release narrower than its production in any category, so a
  128K-only release under a `zx48, zx128` production read as supporting both. Fixed for machines
  first, then generalised: `ProdHardwareService::getInheritedApplicable()` inherits a category only
  when the release states nothing in it. Three places had to move together — the resolver, the
  migration (subtract a category only when it matches the production's exactly, otherwise the
  release loses codes it can no longer inherit back), and the release page, whose `prodHardware`
  chips were built from the raw production set and so displayed codes the release does not carry.
  Measured before deciding, on the restored live data: inheritance gives a machine to 10 338
  releases, of which only 169 are on families where the machine changes emulator selection —
  everything else falls through to `usp` on format alone.

## Open

Nothing outstanding.

## Done after the rollout

- **Form hints.** The production and release hardware fields each carry one muted caption line
  (`prod-form.hardware-hint`, `release-form.hardware-hint`, three languages): shared by all releases
  on the production, replaces the production's whole category on the release. The rule is not
  guessable from the field label.
- **Releases display their effective set everywhere.** `ProdReleasesService`, the legacy
  `getHardwareInfo()` and the public `/api/` release `hardwareInfo` all show own ∪ inherited; after
  the migration a release's own set is empty for most of the catalogue, so rows built from it were
  blank. `hardwareRequired` stays the raw own codes. The release page keeps the visual split.
- **The management menu entry** lives in the login popover, gated on `editHardware`
  (`menu.manage`).
