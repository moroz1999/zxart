# ZX Spectrum Next emulator — implementation

Domain rules: [../domain/release.md](../domain/release.md#running-in-the-browser).

The Next runs on MAME's `tbblue` driver, compiled to WebAssembly, and is unlike
the other emulators here in the one way that shapes everything else: it boots
the **real NextZXOS** from an SD card, so a release is not handed over as a file
to open — it is written onto that card, and the card is built in the browser
before the machine starts.

## Runtime

`htdocs/libs/mamenextsam/` holds the MAME core (`mame.js`, `mame.wasm`), the
Emularity loader, `roms/tbblue.zip` and `next/nextzxos.zip` — the NextZXOS
system tree the card is built around. Its `README.md` carries the MAME version,
the build command, the pinned NextZXOS distribution and the licensing terms;
read it before changing anything in there.

The same directory serves the SAM Coupé, which mounts a file MAME opens directly
and needs no card; it runs at 576x550 and shares `mame-canvas.ts` and
`mame-memory-fs.ts` with the Next. TSConf has a core of its own in
`htdocs/libs/mame/` — see [tsconf-emulator.md](tsconf-emulator.md).

`.wasm` is in the `mod_deflate` list in `htdocs/.htaccess` — the core is 30 MB
raw and about 8 MB compressed — and that rule covers every core the site serves.

The Next runs at 720x576, nudged back into shape by `mame-canvas.ts`; why that
is needed is in
[tsconf-emulator.md](tsconf-emulator.md#the-size-in-the-dialog).

## Building the card

`ZxNextEngine` (`ng-zxart/src/app/features/emulator/engines/zxnext.engine.ts`)
downloads two things and turns them into one image:

| Module | What it does |
| --- | --- |
| `zip-archive.ts` | reads a ZIP with the browser's own `DecompressionStream` — the system tree and the release both arrive as one |
| `release-files.ts` | downloads the release and unpacks it, shared with the TSConf engine |
| `fat-card.ts` | writes a fresh FAT32 volume holding those files, ready for MAME to mount as `hard1`; the TSConf engine builds its card with the same writer |

The card is sized for what it has to hold, as a **power of two bytes from 64 MB
to 512 MB**. The shape is not free: MAME guesses a card's geometry from the
image size and only a size that factorises exactly covers every sector, and its
SD device needs the block count to be a multiple of 1024 to present the image as
SDHC. 64 MB is the floor because below it a volume of 512-byte clusters no
longer reaches the 65525 clusters FAT32 must have; the ceiling is the browser's
memory, since the image is built whole. A release past it is refused with a
message rather than a failure, as is one in an archive the browser cannot open.

**The 8.3 name is what has to be right.** The Next's boot ROM and NextZXOS open
their own files by it — `machines/next/enNxtmmc.rom` among them — so a name that
fits 8.3 is filed under exactly its own upper-cased form. A name in one case
throughout carries the NT case flags and needs nothing else; a mixed-case name
keeps a long entry beside that same short name. Only a name that does not fit
8.3 at all gets an invented `~1` short name, and those are reserved after every
real one, so an invented name can never take the one a file has a right to.

Nothing about the card is stored or generated on the server: the system tree is
one cacheable download, and the release is written into a fresh copy per play.

That last part has to be said to the loader, in `mame-memory-fs.ts`, for this
engine and TSConf alike: Emularity mirrors every file it mounts into IndexedDB
and then **refuses to overwrite one it already has**, so the first card ever
written would be the one every later release booted from — and a card is 64 MB a
play that nothing reads back, so the store grows until the browser refuses
writes and a start hangs with the card half-written. Telling the loader that the
IndexedDB backend is unavailable keeps the whole filesystem in memory.

## Staging a release

Every file of the release lands **under one card-root folder**, `/zxart`, with
the launch file's own directory flattened onto it — so
`InfernoDash/bin/screens/intro.nimg` becomes `/zxart/bin/screens/intro.nimg`
beside `/zxart/game.nex`. Titles build data paths from where they run, so what
sat beside the program in the archive has to sit beside it on the card.

Which file that is comes from the backend, as `launchFilePath`; see
[release.md](release.md).

## Starting it

NextZXOS runs `c:/nextzxos/autoexec.bas` at the end of its boot, and that is
where the release is started from. The engine writes a one-line NextBASIC
program in the +3DOS file the OS loads it from: a 128-byte header and the line.

The program is two lines: `.cd /zxart`, and then whatever starts the release,
following NextZXOS's own rules in `c:/nextzxos/browser.cfg`:

| Format | Statement |
| --- | --- |
| `nex` | `.nexload <name>` |
| `dot` | `../<name>` — a dot command named by path, not by the name of one in `c:/dot` |
| `snx` | `SPECTRUM "<name>"` |
| `bas` | `LOAD "<name>"`, which runs itself when saved with `LINE` |

The first two are plain text; the other two are one BASIC keyword and a quoted
name, so the generator emits the keyword's token. **Each part of this is
forced**:

- **Stand in the folder first.** A title opens its own data files against the
  current directory, which at boot is the root of the card. Aliens: Neoplasma
  reads `data/data0.bin` and shows a black screen when started from anywhere
  else.
- **No drive letter.** `:` ends a statement in NextBASIC, so `c:/zxart` reaches
  the command as a bare `c` and it answers `'c' does not exist`. A leading slash
  is the root of the current drive, which at boot is the card.
- **No quotes.** `.nexload` takes its argument raw; a quoted name fails.
- **No spaces in the name.** A space ends the argument, so the launch file — and
  only it — is staged under a name with its spaces turned into underscores.
  Nothing reads a `.nex` by name once it is loaded.

Only these four formats are started this way: their rule in `browser.cfg` is a
single statement. The rest need several, with variables and line numbers — a
tape goes through the TAP Loader, a `.z80` through `snapload.bas` — so such a
release is staged on the card all the same and the machine boots to the NextZXOS
menu, where the Browser starts it with the OS's own rules.

## Checking it outside the app

`htdocs/libs/mamenextsam/next.html` boots the Next from a ready-made card
(`software/next.img`, written by `build/make-nextzxos-assets.sh` with the
releases in `build/releases.txt`) with nothing of the site around it.
`next-bench.html` runs the same machine with video, sound and throttling off and
prints the average speed MAME measures for itself. NextZXOS holds the machine at
28 MHz even at its own menu, so that cost belongs to the platform rather than to
any one release.

## Limits

- **Only ZIP archives are unpacked in the browser.** `release-files.ts`
  recognises RAR, 7z, gzip and bzip2 and refuses them by name rather than
  staging one unreadable blob under the program's name.
- **Only `.nex`, `.dot`, `.snx` and `.bas` start by themselves.** Everything
  else is left on the card for the NextZXOS Browser.
- **The card is rebuilt per play**, so nothing a release writes survives the
  dialog being closed. Closing it reloads the page, which is the only thing that
  unloads an Emscripten emulator; see `EmulatorModalService`.
- Screenshot capture (F2) and the dialog's restart button are not wired for this
  engine; MAME resets on its own F3.
