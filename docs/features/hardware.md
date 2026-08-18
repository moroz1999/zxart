# Hardware — implementation

Domain rules: [../domain/hardware.md](../domain/hardware.md).

The machines, storage, operating systems, sound chips, controllers and expansions
a production runs on.

## Where hardware lives

- **The catalog** is `engine_hardware` + `engine_hardware_name`: a code, a
  category, a display position, and a name and short name per interface language
  (`en`/`ru`/`es`, see `ZxArt\Shared\InterfaceLanguage`). It is edited at
  `/manage/hardware` behind the `editHardware` privilege.
- **Productions** carry the shared set on `zxProd.hardwareRequired`.
- **Releases** carry only their own deviations on `zxRelease.hardwareRequired`.

Link tables store the catalog **id**; every layer above the database works in
**codes**, which the `DBValueSet` chunk translates in both directions. Codes are
the public contract — the `/api/` filters and every REST payload use them.

Three sets answer three different questions — keep them apart:

| method | on | returns | used by |
|---|---|---|---|
| `hardwareRequired` / `getHardwareCodes()` | both | the element's own codes | edit forms; **what a production displays** |
| `getEffectiveHardwareCodes()` | release | own **gap-filled from the production** | **what a release displays**; emulator resolution, playable files, structured data |
| `getAggregatedHardwareCodes()` | production | own ∪ **releases'** | the catalogue filter, stats, import de-duplication, list image preset — never displayed |

### What each view shows

A **release** is always shown what it *runs on* — its own codes plus what it
inherits. It records only deviations, so its own set is empty for most of the
catalogue and a row built from it would simply be blank. This holds for the
release rows on a production page, the legacy detail template and the public
`/api/` alike. The release page is the one place that splits the two apart, and
only visually: the production's codes as plain chips, the release's own in the
accent colour.

### A production shows its own set, never the aggregate

Both the card and the detail page print `hardwareRequired`. A production is
described by the set its releases share — in practice what the first release
established — not by everything any release ever needed. Aggregating would spell
a GS soundtrack and a microdrive edition onto the card of a production that is
plainly a 48K game, which says less about it rather than more. The variants stay
visible where they belong: on the release rows, as the deviations they are.

Searching is the other way round and deliberately so. The catalogue filter
matches the **aggregate**, so a search for a code only one release needs still
finds the production. It is found by it without being labelled with it.

This holds for the public `/api/` too: `hardware`, `hardwareInfo` and
`hardwareString` on a production are its own set, while `zxProdHardware` as a
filter matches the aggregate.

Only three things still read the aggregate, and none of them prints it:
`zxProdHardware` in the catalogue filter, `HardwareCompatibilityService` for
import de-duplication (it must, or every later import creates a duplicate
production), and `getListImagePreset()`, which needs the full picture to decide
whether any release warrants the hi-res image.

Names never come from the frontend: every response carries `name`, `shortName`
and `category` for the request language.

### Inheritance fills gaps, it never widens a statement

A release takes the production's codes **only in the categories it says nothing
about**. It is not a union, and the distinction is the whole reason the split is
safe.

A release that lists machines has listed all of them — it was built for those and
no others — so a production saying `zx48, zx128` must not turn a 128K-only
release into one claiming both. The same holds category by category: a release
naming `3dos` is not also on TR-DOS because its production is, and one naming
`beeper` did not grow an AY. Where the release is silent the production's list is
the best knowledge there is, and that is what lets a release carrying no hardware
of its own still resolve a machine.

The production is free to state more than some release does — it is collected
from the `original` releases, and a re-release or an adaptation may well be
narrower. That is exactly why inheritance has to work this way: a 128K-only
re-release under a `zx48, zx128` production keeps saying `zx128` and no more.

`ProdHardwareService::getInheritedApplicable()` is the single implementation. The
release page asks it for the chips it shows as the production's, so the page can
never display a code the release does not actually carry.

One consequence is worth knowing before it surprises anyone: a release stating no
hardware at all inherits everything, and if the production requires `gs` the
release becomes unplayable, because General Sound is the one code the online
emulators refuse. Six releases in the catalogue are in that position. It is the
right answer — the earlier "playable" rested on the release saying nothing, not
on it being GS-free — but it does remove a button that used to work.

## Auto-fill from the release format

`ZxArt\Releases\Services\ReleaseHardwareAutofillService` derives the hardware a
release's file format implies. It runs from
`zxReleaseElement::updateFileStructure()`, which every save path goes through, so
re-saving a release re-checks its formats.

Rules are **additive and idempotent**: they only add codes, never remove what an
editor chose, and a second run changes nothing. "Add nothing" is the default — a
format used by two machines produces no code unless something else disambiguates
it.

Everything is decided from the release's **effective** set
(`getEffectiveHardwareCodes()`), never its own codes alone, and that set does two
jobs at once.

It is where the **machine** comes from. A release seldom names one; its
production does. Stage 2 below needs a machine family, so reading only the
release's own codes would leave it unresolvable for most releases.

It is also what **already present** means. A code the production states is not
missing, and re-adding it to the release would push a shared code back down and
undo the prod-hardware split on every save.

What does get added is still written to the release: a code that is genuinely new
there is release-specific, which is exactly what the release layer holds.

### What a file format can and cannot say

A disk-image extension names the **medium**, not the filesystem on it. Beta Disk
images ship as `.trd`, `.scl`, `.fdi` and `.udi` and carry TR-DOS, CP/M or iS-DOS
alike — CP/M `.trd` files exist, and iS-DOS is distributed as `.fdi`/`.udi`. So a
container contributes its interface and nothing more.

An operating system is only derived from an extension that names one system:
`.cpm`, `.opd`, `.tar`. Everything else leaves the DOS unset, for an editor to
fill in.

### Stage 1 — the format alone

Applied always; no machine needed.

| format | adds | why |
|---|---|---|
| `tap`, `tzx` | `tape` | the only medium these encode |
| `mdr` | `microdrive` | |
| `trd`, `scl` | `betadisk` | Beta Disk containers; the filesystem inside is not implied |
| `fdi`, `udi`, `td0` | `betadisk` | Beta Disk containers |
| `cpm` | `cpm` | |
| `opd` | `opd` | Opus Discovery |
| `d40`, `d80` | `mdos` | Didaktik disk images; the drive interface has no catalog code, so only the DOS |
| `mbd` | `mb02` | |
| `mld` | `dandanator` | |
| `dck` | `timex_cartridge` | |
| `spg` | `tsconf` | the format exists only for TSConf |
| `nex`, `snx` | `zxnext` | |
| `sad` | `samdos` | |
| `tar` | `esxdos` | the esxdos/divMMC archive format |

### Stage 2 — format × machine family

For formats that are ambiguous on their own. The **compatibility family** comes
from `HardwareCompatibilityRules::codesToGroups()` applied to the effective set —
in practice to the machine the production carries. A family is a set of machines that run each other's
software, not a machine: `spectrum` covers the Sinclair models together with the
clones — Pentagon, Scorpion, Profi, Byte, Timex, Didaktik.

| format | family | adds |
|---|---|---|
| `dsk` | `spectrum` | `3dosdisk` — the drive; the +3 shipped with CP/M Plus, so not `3dos` |
| `dsk` | `samcoupe` | `samdos` |
| `mgt` | `spectrum` | `gdos` |
| `mgt` | `samcoupe` | `samdos` |

More than one family, or a family with no row: nothing is added.

`mgt` on a Spectrum adds `gdos` rather than `disciple` + `plusd`. Those are
*alternative* interfaces sharing the G+DOS format — a release needs one of them
and the format cannot say which, so only the DOS is determinable.

### Guards

- **A derived DOS is written only when the release has none and exactly one
  candidate was produced.** Occupied by any of `cpm`, `isdos`, `tasis`, `nedoos`,
  `mdos`, `tos`, `bsdos`, `3dos`, `esxdos`, `disciple`, `gdos`, `opd`, `samdos`,
  `trdos`, `trdos4x`. Two formats implying different systems get neither — they
  contradict each other.
- **Storage is never blocked by an existing DOS.** A +3 drive is still the medium
  whatever DOS runs on it: `dsk` + `zx128+3` + `cpm` yields `3dosdisk`.
- **Every code is checked against the catalog** before it is written, so a rule
  can never produce a code that cannot be stored.

### Formats with no rule

`bin`, `rom`, `img`, `z80`, `sna`, `szx`, `slt`, `$b`, `$c`. Snapshots and raw
binaries say nothing about hardware. The closest call is `$c`, which leans
towards IDE interfaces (`nemoide` 30, `smuc` 29, `atmide` 22 of 67 releases), but
no single code dominates.

## Deploying

The two migrations and the code go out together, in this order:

1. `db/migrations/2026.08.08 - hardware catalog.sql` — the catalog, the release
   link table's move from `value` to `hardwareId`, the privilege grant.
2. `db/migrations/2026.08.09 - prod hardware.sql` — the production link table.
3. The code.
4. **Drop the element cache.** This step is not optional and is easy to forget.

Step 4 matters because the cache stores serialized structure elements, data
chunks included, so a cached element keeps the *old* chunk configuration however
new the deployed code is. `hardwareRequired` changed on both sides of that
boundary — the release chunk now reads `hardwareId` instead of `value`, and the
production gained a chunk it did not have — so stale entries produce errors that
look like a half-finished deploy but are not:

```
Unknown column 'value' in 'SELECT'
  (select `value`, `id` from `engine_module_zxrelease_hw_required` ...)
zxProdElement::getHardwareCodes(): Return value must be of type array, null returned
```

The first is a cached release chunk still asking for the dropped column; the
second is a cached production that has no `hardwareRequired` chunk at all, so
`__get()` answers null. Both clear the moment the cache does.

## One-off jobs

Both are additive, resumable and safe to re-run. **Run them in this order**, and
only once the deploy above is complete.

1. `/fix/job:prod-hardware-migrate/` — moves each production's shared hardware off
   its releases. `dry:1`, `force:1`, `offset:N`, `limit:N`. Two steps, and the
   second is what makes the first safe:

   - **The production takes the intersection of its `original` releases**, or of
     every release with hardware when there is no original. Releases carrying no
     hardware take no part; an empty intersection leaves the production alone.
   - **Every release then drops the categories it states exactly as the
     production does** — all releases, not just the sources.

   Subtraction is per category and exact for the same reason inheritance is: a
   release does not inherit a category it says anything about, so removing part
   of one would strand the remainder. A release supporting `kempston, sinclair2`
   under a production requiring `kempston` would be left owning `sinclair2`,
   inherit nothing back, and claim the joystick it merely added while losing the
   one it needs. Where the two sets match, the release inherits the category
   straight back.

   Subtracting the shared set **as a whole** is what damaged the live data on the
   first production run: a 128K-only re-release under originals carrying
   `zx48, zx128` had `zx128` taken as "already said" and inherited both machines
   back. Per category it keeps `zx128` and stays 128K-only.

   **It must not run before the import rerouting is deployed.** Import
   de-duplication compares hardware to decide whether an incoming production
   already exists; once the shared codes are off the releases it has to compare
   the aggregated set, or every later import creates duplicate productions.

2. `/fix/job:hardware-autofill/` — applies the rules above to every release with a
   format. `dry:1` prints the diff, `offset:N` / `limit:N` batch it.

The order is what makes stage 2 work at scale: it takes the machine from the
production, so until the migration has put one there, the disk releases of an
unmigrated production resolve no family and get only what stage 1 derives.
Running the backfill first is not harmful — both jobs are additive and
idempotent — but it would have to be repeated afterwards to pick those releases
up. Measured on the live catalogue, this is what the ordering is worth: 90
releases whose `dsk`/`mgt` format names no machine of their own, 80 of which get
a single machine family from their migrated production and so a `gdos` or
`3dosdisk` out of stage 2.

The remaining 10 stay empty, and no rule can reach them: their production's
sources disagree on the machine or name none at all, so the intersection holds no
computer.
