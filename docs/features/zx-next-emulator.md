# ZX Spectrum Next emulator — implementation

Domain rules: [../domain/release.md](../domain/release.md#running-in-the-browser).

The Next runs on MAME's `tbblue` driver, compiled to WebAssembly, and is unlike
the other emulators here in the one way that shapes everything else: it boots
the **real NextZXOS** from an SD card, so a release is not handed over as a file
to open — it is written onto that card, and the card is built in the browser
before the machine starts.

## Runtime

The Next's own files in `htdocs/libs/mame/` are `roms/tbblue.zip` and
`next/nextzxos.zip` — the NextZXOS system tree the card is built around. The
core it shares with every other machine here is described in
[mame-machines.md](mame-machines.md), and the directory's `README.md` carries
the pinned NextZXOS distribution and the licensing terms; read it before
changing anything in there.

The machine is pinned to boot ROM **3.01.00** (`-bios v30100`), the one that
distribution goes with, and launched at 720x576 with `-aspect 2:1` — the Next's
picture is 360x288 at its widest, so that is whole pixels of it.

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

The card is handed to the loader as a `blob:` URL, which it mounts and does not
cache — see [mame-machines.md](mame-machines.md#handing-the-machine-a-file). A
card is 64 MB a play that nothing ever reads back, so caching one would fill the
store a release at a time.

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

`htdocs/libs/mame/next.html` boots the Next from a ready-made card
(`software/next.img`, written by `build/make-nextzxos-assets.sh` with the
releases in `build/releases.txt`) with nothing of the site around it. It is one
of the reference pages described in [mame-machines.md](mame-machines.md).

## Limits

- **Only ZIP archives are unpacked in the browser.** `release-files.ts`
  recognises RAR, 7z, gzip and bzip2 and refuses them by name rather than
  staging one unreadable blob under the program's name.
- **Only `.nex`, `.dot`, `.snx` and `.bas` start by themselves.** Everything
  else is left on the card for the NextZXOS Browser.
- **The card is rebuilt per play**, so nothing a release writes survives the
  dialog being closed. Closing it reloads the page, which is the only thing that
  unloads an Emscripten emulator; see `EmulatorModalService`.
- Screenshot capture (F2) is not wired for this engine.
