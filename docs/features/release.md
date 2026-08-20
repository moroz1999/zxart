# Releases — implementation

Domain rules: [../domain/release.md](../domain/release.md).

### Purpose
Concrete release (version) of software production. Contains files specific to this version. Always linked to parent zxProd.

### Main Fields
- **title** - release title
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
- The parsed structure is built by `ZxArt\FileParsing\ZxParsingManager` on top of `moroz1999/zx-files`. One `ZxParsingItemContainer` covers every container the library reads — TRD, SCL, TAP, TZX, DSK, FDI, UDI, OPD and TAR — while ZIP and RAR keep items of their own; `ZxParsingManager::createItem()` is the single place that maps a type to an item, used by the top-level parse, by the archive items and when rebuilding the tree from the registry.
- The file extension decides whether a file is opened as a container at all (`ContainerFormat::fromExtension()`, which also knows `opu` and `edsk`). It is then handed to the library as a hint, and the library picks the parser that actually recognises the bytes, so a mislabelled file still parses. The registry row's `type` stays the declared format, and every value it can take must exist in the `engine_files_registry.type` enum.
- Containers with real directories — IS-DOS disks, TAR trees — produce folder rows exactly like a ZIP does; the flat systems put everything under the container.
- File names are kept as recorded on the media. IS-DOS names come back through the library's CP866 conversion, and anything else that is not already UTF-8 is read as the ZX Spectrum character set, which is what turns an Opus name's trailing keyword byte into readable text.
- `zx_basic` covers `.b` (TR-DOS, tape) and `.bas` (+3DOS, esxDOS); the 128 byte `PLUS3DOS` header is dropped before the listing is decoded.
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

### Special Operations
- **clone** - creates a copy of the release under the same parent prod, carries over hardware, language, publishers and authorship, and resets usage counters. Gated by the `clone` privilege, which `publicAdd` grants to the release author. The release details editing controls run it through `/ajax/` behind a confirmation dialog and navigate to the clone.

### Emulator Launch Capability
Determined by combination of:
1. **releaseType** - file type must be in runnable list
2. **hardware** - the effective set (the release's own plus its production's) must be supported by the emulator

#### Launch Rules:
- **ZX Spectrum (USP)**: formats `trd`, `tap`, `z80`, `sna`, `tzx`, `scl`
- **ZX81**: formats `tzx`, `p`, `o` + ZX81 hardware
- **ZX80**: ZX80 hardware
- **TSConf**: formats `spg`, `img`, `trd`, `scl` + TSConf hardware
- **MB (Multiboard)**: format `tar` + MB hardware
- **Timex (JSSpeccy)**: formats `tap`, `tzx`, `z80`, `sna`, `szx` + `timex2048` / `timex2068` hardware. Matched before the USP fallback, which would otherwise swallow every Timex release, since only the machine says the SCLD video modes have to be emulated. Each model is its own emulator id, because JSSpeccy boots one machine and TC2048 and TC2068 are not the same one. Cartridges (`dck`) are the Timex-only format and JSSpeccy cannot load them, so a release distributed as one stays unplayable.
- `EmulatorResolverService::UNSUPPORTED_HARDWARE` lists hardware the online emulators cannot emulate (currently General Sound, `gs`). It is a sound extension, so it suppresses the emulator only when the effective set names no other sound: with `ay`, `beeper` or any other code in the sound category present, the release stays playable and only the GS track is lost. When it does suppress, `resolveEmulator()` returns `null`, so `isPlayable()`/`getEmulatorType()` are false/null and the play button is hidden. The category comes from `HardwareCatalogService::getCategoryOf()`, which is why the resolver is no longer dependency-free.

#### Angular Prod Details Emulator
- Prod details release rows pass the ZIP play URL to USP and the first runnable file URL to non-USP emulators.
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
