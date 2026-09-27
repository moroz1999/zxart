# Releases — implementation

Domain rules: [../domain/release.md](../domain/release.md).

### Purpose
Concrete release (version) of software production. Contains files specific to this version. Always linked to parent zxProd.

### Main Fields
- **title** - release title. Optional: `publicAdd` names a new release after the
  file it was uploaded with, and `persistElementData()` falls back to the parent
  production's title, so a release always carries a name and the public pages
  need no fallback of their own.
- **version** - release version
- **year** - release year
- **description** - release description (HTML)
- **file** - main release file
- **fileName** - file name

### Relations with Other Entities

#### Parent Product
- **zxProd** - parent product (link `structure`, role child)
  - Each release must belong to one prod
  - Link through structural hierarchy
  - Required in both the Angular form and the element action (`notEmpty`). Unlike
    the other works there is no sensible fallback, so a release with no
    production is rejected: creation answers 422 and nothing is stored.

#### Authorship
- **authors** - authors with roles (code, graphics, music, etc.)
  - Can differ from prod authors (e.g., for ports)

#### Publishers
- **publishers** - release publishers (link `zxReleasePublishers`, role child)
  - Can differ from prod publishers

#### Compilations
- **compilations** - compilations that include this release (link `compilation`, role child)

### Technical Characteristics

#### Release Type
- **releaseType** - release type:
  - `tar` - TAR archive (for MB)
  - `trd` - TR-DOS disk
  - `tap` - TAP file
  - `z80` - Z80 snapshot
  - `sna` - SNA snapshot
  - `tzx` - TZX file
  - `scl` - SCL disk
  - `p` - ZX81 program
  - `o` - ZX81 program
  - `spg` - TSConf SPG
  - `img` - disk image
  - and other formats

#### Release Format
- **releaseFormat** - release format(s) (array)
  - Can contain multiple formats for one release
  - Stored in table `module_zxrelease_format`
  - Derived from the parsed structure by `ReleaseFileTypesGatherer`, which walks
    only what holds files in their own right: the top-level file, and whatever a
    folder or an archive holds beside it. `ZxParsingItem::holdsSeparateFiles()`
    is what says so — true for a folder, a ZIP, a RAR and a TAR distribution
    tree, false for every disk and tape image.
  - A disk or a tape is therefore not opened for this. Its catalogue names are
    that image's contents, not files that were published, and reading them as
    formats is what filed plain TRD images under `trd, o` and `trd, p, o`.
  - `/fix/job:release-formats/` re-derives the stored formats from the parsed
    structure — `dry:1` prints the diff, `offset:N`/`limit:N` work in batches.
    Nothing is read from disk, so a release whose file has not changed is not
    re-parsed.

#### Hardware Requirements
- **hardwareRequired** - the release's **own** hardware, i.e. what it needs beyond
  what its production already states (array)
  - Stored in `module_zxrelease_hw_required` as catalog ids; the property works in codes
  - `getEffectiveHardwareCodes()` adds the production's set and is what decides
    behaviour — emulator launch, playable files, list image preset. The raw
    property is for the edit form and for showing what belongs to this release.
  - Responses carry the name, short name and category of each code, localized for
    the request language; the SPA does not translate hardware.
  - Never derived on save; the hardware a file format implies is applied only by
    the `/fix/job:hardware-autofill/` backfill — see [hardware.md](hardware.md).

#### Languages
- **language** - interface languages (array)
  - Stored in table `zxitem_language`

### Files and Media
- **screenshotsSelector** - release screenshots
- **inlayFilesSelector** - inlay files (covers)
- **infoFilesSelector** - information files
- **adFilesSelector** - advertising materials
- Release details expose `inlayFilesSelector` and `adFilesSelector` together as `covers`, grouped by kind exactly like a prod's covers (`{kind, items}` per `ProdCoverKind`, empty kinds omitted); the page renders one headed section per group and `tabs.hasCovers` follows both selectors.
- Release cover images share a 200px display height, while each tile derives its width from the thumbnail's aspect ratio.
- Release details permits users with `publicReceive` privilege to reorder screenshots stored in `screenshotsSelector` through the shared screenshot move API.
- Removing a file from a selector in the release or prod form deletes it at once through `deleteFile` of the entity's data endpoint (`/release-data/`, `/prod-data/`). Only a file of that entity's own selectors is accepted (`404` otherwise); the file element itself must grant `delete`. The form reports a failure.
- The parsed structure is built by `ZxArt\FileParsing\ZxParsingManager` on top of `moroz1999/zx-files`. One `ZxParsingItemContainer` covers every container the library reads — TRD, SCL, TAP, TZX, DSK, FDI, UDI, OPD, MGT, IMG, D40/D80 and TAR — while ZIP and RAR keep items of their own; `ZxParsingManager::createItem()` is the single place that maps a type to an item, used by the top-level parse, by the archive items and when rebuilding the tree from the registry.
- The file extension decides whether a file is opened as a container at all (`ContainerFormat::fromExtension()`, which also knows `opu`, `edsk` and `d40`, the last read as a `d80`). It is then handed to the library as a hint, and the library picks the parser that actually recognises the bytes, so a mislabelled file still parses. The registry row's `type` stays the declared format, and every value it can take must exist in the `engine_files_registry.type` enum.
- `ArchiveFileResolverService` decides which files inside the archive are releases in their own right, pooling the types of every machine in the effective set. `EXCLUDED_FILE_TYPES` takes one back out per machine: the Next never offers a `.bin`, since a Next title keeps its data in those beside the program.
- The formats a release was published in come from `ReleaseFormatsProvider::FORMATS`, and every value there must exist in the `engine_module_zxrelease_format.value` enum — an unlisted one is stored as an empty string and the release shows no format at all. They are **named on the front end**, under `release-format.<code>` in ng-zxart's i18n, rendered through `ReleaseFormatLabelPipe`; the API carries the code and its emoji, not a label. The prods-list filter and the `/formdata/` option list still take their titles from the `zxRelease.filetype_*` database translations — legacy, see [../i18n.md](../i18n.md).
- Containers with real directories — IS-DOS disks, TAR trees — produce folder rows exactly like a ZIP does; the flat systems put everything under the container.
- File names are kept as recorded on the media. IS-DOS names come back through the library's CP866 conversion, and anything else that is not already UTF-8 is read as the ZX Spectrum character set, which is what turns an Opus name's trailing keyword byte into readable text.
- `zx_basic` covers `.b` (TR-DOS, tape) and `.bas` (+3DOS, esxDOS); the 128 byte `PLUS3DOS` header is dropped before the listing is decoded.
- A container that reads a file system may say outright what a file is, through `ZxParsingItem::setInternalType()`, and then nothing is guessed from the name: GDOS records no extension at all and MDOS writes the type letter as one, so a BASIC program on a +D or a Didaktik disk is only recognisable from its catalogue record. SAM BASIC is left out of it — its tokens are not the ZX Spectrum ones and it would not list.
- GDOS and SAMDOS keep a nine byte header in front of a file, repeating what the catalogue already records; the parsed item holds the file without it, which is also what makes a SCREEN$ the 6912 bytes the image types recognise. Every other system stores its files as they are.
- Parsed release structure exposes downloadable archive entries. File downloads are triggered from the Angular release details UI as button actions, while file previews are loaded through `/release-file-content/` and rendered in a dialog instead of linking to legacy `viewFile` pages.
- The parsed file structure is only built and returned when `isDownloadable()` is true (`fileStructure` is empty otherwise, which also hides the Structure tab). This mirrors the legacy gate `{if $element->parsed && $element->isDownloadable()}` and prevents per-file download links from leaking for non-downloadable releases (e.g. `insales` prods, or old forbidden prods to anonymous users).

### Download Gating (legalStatus)
- `zxReleaseElement::isDownloadable()` is the single source of truth for whether a release file may be downloaded; the release inherits its legal status from its parent prod (`getLegalStatus()` delegates to the prod).
- A release is downloadable when its (prod's) status is not `forbidden`/`forbiddenzxart`/`insales`, OR it is a `demoversion` release, OR the `downloadDenied` privilege is set, OR — for non-`insales` statuses only — the current user is authorized and the prod year is known and older than 20 years (the "old prods for registered users" case).
- `insales` ("in sales") is always excluded, including from the old-prod allowance: such prods/releases must never expose a download link. The legacy release row shows a "purchase" external-link button instead.
- API responses gate `downloadUrl` (and the parsed `fileStructure`) by `isDownloadable()`, evaluated per request against the current session, so authorized-only download URLs are never emitted to anonymous users.
- The release hero bar offers the prod's external link as a call to action when the prod carries one: a "buy" button for `insales` and a "donate" button for `donationware` (`prodLegalStatus` and `prodExternalLink` on the release details response).
- Known residual risk (pre-existing in legacy, UI-only protection): the `releasefile` and `zxfile` download applications themselves do not enforce `isDownloadable()`; protection relies on hiding the link rather than blocking the endpoint.
- Parsed release structure file names are URL-decoded for display only; download and preview lookup URLs continue to use the original stored archive entry data.
- Parsed release structure can play TAP and supported TZX entries as generated browser audio from the Angular release details UI.
- Release table thumbnails show an animated larger first-screenshot preview on pointer hover.

### Usage Statistics
- **downloads** - number of downloads
- **plays** - number of emulator launches

### Voting and Comments
- **votes** - average rating
- **votesAmount** - number of votes
- **denyVoting** - deny voting
- **commentsAmount** - number of comments
- **denyComments** - deny comments
- Selected release legacy details page displays the shared ZX item voting controls for the release itself.

### Metadata
- **dateAdded** - date added
- **userId** - ID of user who added the element
- **parsed** - flag that file was parsed

### Creating releases
The creation form takes **several files at once** and posts them in one request
as `fields[file][]`. `ZxArt\Releases\Services\ReleaseBatchCreateService` is what
splits it: PHP hands a single upload over as its own properties and several as a
list of those, which is what tells the two apart, and each file is submitted
through `FormCreateService` with the rest of the form beside it. So a production
gains as many releases as files were picked, all sharing the values that were
typed once, and a submit with no file still creates the one release the form
describes.

The screenshots, inlays and other files beside them go to **every** release, as
the very same staged upload: receiving one copies it out of the upload cache and
leaves it there for the next release to read. The service deletes them once the
last release has, whether the batch finished or failed — see
[../cms.md](../cms.md#staged-uploads).

`/formdata/` answers with `ids` — every release created, in upload order — and
`id`, the first of them. One created release lands on its own page, several
return to the production. Editing an existing release keeps the single-file
field.

### Special Operations
- The release page offers editing only with the `publicReceive` privilege, the
  same privilege that guards and saves the edit form.
- **clone** - creates a copy of the release under the same parent prod, carries over hardware, language, publishers and authorship, resets usage counters, and records the cloning user and the current time as who added it and when. Gated by the `clone` privilege, which `publicAdd` grants to the release author. The release details editing controls run it through `/ajax/` behind a confirmation dialog and navigate to the clone.

### Emulator Launch Capability
Determined by combination of:
1. **releaseType** - file type must be in runnable list
2. **hardware** - the effective set (the release's own plus its production's) must be supported by the emulator

#### Launch Rules:
- **ZX Spectrum (USP)**: formats `trd`, `tap`, `z80`, `sna`, `tzx`, `scl`
- **ZX81**: formats `tzx`, `p`, `o` + ZX81 hardware
- **ZX80**: ZX80 hardware
- **TSConf (MAME)**: `tsconf` hardware alone, like the Next; the runnable formats (`spg`, `img`, `trd`, `scl`) are what the launch-file lookup below ranks. See [tsconf-emulator.md](tsconf-emulator.md).
- **Scorpion, ATM Turbo, Profi, ZX Evolution, Sprinter (MAME)**: `TRDOS_MACHINE_EXTENSIONS` (`trd`, `scl`, `z80`, `sna`) + that machine's hardware (`scorpion`/`scorpion1024`, `atm`/`atm2`, `profi`, `baseconf`/`zxevolution`, `sprinter`). Matched before the USP fallback for the reason Timex is: a TR-DOS disk is a Spectrum release by format, and only the machine says its memory, video and disk system have to be emulated. A disk these machines boot and a snapshot their snapshot device loads and runs; **a tape is left out on purpose**, because MAME would come up at BASIC with it in the deck and nothing to press play, while the fallback loads it. See [mame-machines.md](mame-machines.md).
- **MB (Multiboard)**: format `tar` + MB hardware
- **ZX Spectrum Next (MAME)**: `zxnext` hardware alone, with no format condition, because the release is mounted on the emulated SD card whole rather than opened as one file. For both these machines `zxReleaseElement::resolveEmulatorType()` then asks for a launch file and answers `null` without one, which keeps the play button off a release with nothing to run; it asks the resolver for their runnable types directly, since `getRunnableTypes()` would come back to it. See [zx-next-emulator.md](zx-next-emulator.md).
- **Timex (JSSpeccy)**: formats `tap`, `tzx`, `z80`, `sna`, `szx` + `timex2048` / `timex2068` hardware. Matched before the USP fallback, which would otherwise swallow every Timex release, since only the machine says the SCLD video modes have to be emulated. Each model is its own emulator id, because JSSpeccy boots one machine and TC2048 and TC2068 are not the same one. Cartridges (`dck`) are the Timex-only format and JSSpeccy cannot load them, so a release distributed as one stays unplayable.
- `EmulatorResolverService::UNSUPPORTED_HARDWARE` lists hardware only some emulators can be (currently General Sound, `gs` and `ngs` — one NeoGS card covers both). It is a sound extension, so it suppresses the emulator only when the effective set names no other sound: with `ay`, `beeper` or any other code in the sound category present, the release stays playable and only the GS track is lost. When it does suppress, `resolveEmulator()` returns `null`, so `isPlayable()`/`getEmulatorType()` are false/null and the play button is hidden. The category comes from `HardwareCatalogService::getCategoryOf()`.
- `EmulatorResolverService::UNSUPPORTED_MACHINES` is the machine **no** core here can be — currently the Pentagon 2.666, which neither MAME nor Unreal Speccy Portable emulates. Same shape as the sound rule, against `HardwareGroup::COMPUTERS`: it suppresses the emulator only when the effective set names no other computer, so a 2.666 beside a plain Pentagon costs only the 2.666 version. Without it the format-only fallback would offer such a release as a machine it is not.
- **An ATM release with a General Sound track resolves to `pentevo`.** The ZX Evolution is compatible with the ATM Turbo and has the ZX Bus a NeoGS needs, which the ATM has not.
- **A disk wanting sound the fallback has not resolves to `scorpion`.** `MAME_SOUND_HARDWARE` is what only the MAME machines produce — the NeoGS (`gs`, `ngs`) and the second AY of a TurboSound (`ts`) — and the Scorpion GMX takes both cards while running plain Spectrum software. The rule is last of the machines, so a release that did name one keeps it and is fitted there instead. It needs a format a MAME machine starts by itself — a disk or a snapshot; only a tape stays on the fallback, and loses its second chip there.
- The check runs **after** the match, not before it, because which machine the release resolved to is what decides whether its GS track can be heard. `GENERAL_SOUND_EMULATORS` names the ones carrying a NeoGS — `tsconf`, `scorpion`, `profi`, `pentevo`, `sprinter` — and for those the rule is skipped entirely.

#### Angular Prod Details Emulator
- `EmulatorResolverService::servesWholeArchive()` decides what `playUrl` points at: the release file whole for USP, the Next and TSConf, one file picked out of it for every other emulator. Prod details release rows and the release page both go through it.
- Releases handed the whole file also carry `launchFilePath` (where the entry to start sits inside the release file, what the emulator matches) and `launchFileId` (the same file's id in `fileStructure`). Both are computed per request by `zxReleaseElement::getLaunchFileId()` / `getLaunchFilePath()` and stored nowhere, so a re-parse changes the answer with no migration.
- `getLaunchFile()` ranks every parsed entry whose extension the emulator can run and takes the best, never the first: by extension (`zxReleaseElement::LaunchPriorities` — `nex` > `dot` > `bas` > `snx` > `tap`/`tzx` > `b` for the Next, `spg` > `trd`/`scl` > `img` for TSConf; the two sets do not overlap) and then by depth, so a game's own program beats a loader in its source tree. `getStagedDepth()` skips any entry whose ancestry passes through something other than a folder or an archive, mirroring `ZxParsingItem::holdsSeparateFiles()` — without it a tape's catalogue entries compete with the tape holding them.
- USP uses a 960x720 canvas by default, exactly double the 480x360 emulator viewport.
- Emulator screenshots launched from prod details release rows are saved to the parent prod, not to the release.
- Emulator screenshots launched from the release details page are saved directly to the release. The release details API response carries `canUploadScreenshot`; the upload itself goes to `/screenshot-upload/` with the release id.

#### Emulator dialog
- Every emulator id resolves to one engine in `ng-zxart/src/app/features/emulator/engines/`, and `SUPPORTED_EMULATOR_TYPES` (play button and release card) is what decides whether the play button appears at all — an id with no engine is silently not playable.
- An engine normally draws into the dialog's canvas. One that builds its own interface sets `rendersOwnUi`, mounts into the canvas wrapper it is handed as the third `start()` argument, and the canvas is hidden. The wrapper then gets a width of its own: the dialog is as wide as its content, so a wrapper holding nothing but an emulator that starts at 320px could never grow, leaving the engine no room to scale into.
- JSSpeccy is such an engine: it lays itself out in whole 320x240 steps, so the engine converts the wrapper's size into a zoom level, on start and on window resize, up to the 960x720 the other emulators run at. The zoom is never set while fullscreen — JSSpeccy answers a zoom change by leaving fullscreen, and entering fullscreen fires a resize, so the two would fight; it restores the zoom itself once fullscreen ends. It is deliberately started paused — JSSpeccy creates its `AudioContext` inside `start()` and never resumes it, so pressing its own play button is what gets the sound out. Its runtime lives in `htdocs/libs/jsspeccy/`; `jsspeccy.js`, the worker, the `.wasm` core and the `roms`/`tapeloaders` directories resolve relative to each other and have to stay together.
- The dialog footer credits the emulator behind the running engine and links to its home page, from `EMULATOR_HOMEPAGES`. An emulator whose project link is not known yet is mapped to `null` and no credit is shown.

### Constraints and Rules
1. Release must always have parent zxProd
2. Release contains concrete file, unlike abstract prod
3. One prod can have multiple releases (different versions, platforms, publishers)
4. Release can have its own authors and publishers, different from prod
5. Emulator launch capability is determined automatically by file type and hardware requirements
6. Release file can be parsed to extract metadata (parsed flag)
