# TSConf emulator — implementation

Domain rules: [../domain/release.md](../domain/release.md#running-in-the-browser).

TSConf runs on MAME's `tsconf` driver, compiled to WebAssembly. Like the Next it
is handed the release **whole** rather than one file picked out of it, because a
TSConf program opens its data files off the SD card while it runs.

## Runtime

`htdocs/libs/mame/` holds the core (`mame.js`, `mame.wasm`), the Emularity
loader, the ROMs the devices ask for (`tsconf.zip`, `betadisk.zip`,
`kb_ms_natural.zip`, `zxbus_neogs.zip`), the MAME config that switches the
second AY on (`cfg/tsconf.cfg`) and two saved TS-BIOS configurations
(`nvram/tsconf_trdos`, `nvram/tsconf_sd`) that differ in one setting: which
device the machine boots from.

This is a **separate MAME build** from the one the Next and the SAM Coupé use in
`htdocs/libs/mamenextsam/`, with its own ROMs beside it.

The NeoGS is plugged into `zxbus1` and brings an SD card of its own — which is
why the TSConf's own card is `hard2` and not `hard1`.

## What the machine is handed

`TsconfEngine` (`ng-zxart/src/app/features/emulator/engines/tsconf.engine.ts`)
downloads the release, unpacks a ZIP with `release-files.ts` and takes the file
named by `launchFilePath` (see [release.md](release.md)). That file decides both
the device it is mounted on and the TS-BIOS configuration the machine comes up
in:

| Launch file | Device | TS-BIOS boots from |
| --- | --- | --- |
| `spg` | `dump`, the snapshot device | TR-DOS |
| `trd` | `flop1`, the Beta Disk drive | TR-DOS |
| `scl` | `flop1`, **converted to a TRD first** | TR-DOS |
| `img` | `hard2`, the SD card — the release ships a whole card | the SD |

Whatever the release calls the file, it is mounted as `release.spg`,
`release.trd` or `release.img`: every one of those devices picks its format from
the extension, and a published name may hold spaces, brackets or a second dot.

**MAME's floppy cannot read an SCL.** It answers `Unable to identify image file
format` and the machine never starts. `scl-to-trd.ts` lays the archive out as
the TR-DOS disk it holds — the catalogue, then the files back to back from track
1 — and that TRD is what is mounted.

## The SD card

An SPG is loaded into memory whole and then run, and a title that does not fit
in memory opens the rest of itself by name (Another World reads
`/ANOTHER/bank01` through `/ANOTHER/bank0d`). So an SPG launch also gets a card,
built by `fat-card.ts` — the same FAT32 writer the Next's card comes from —
holding **every file of the release at the path it was published under**, which
is where the release's own instructions say to copy it.

A release that ships an SD card image of its own is mounted as that card
instead. A TR-DOS release gets no card at all: everything it loads is on its
disk.

Nothing is generated on the server and nothing survives the dialog closing.
`mame-memory-fs.ts` keeps the loader's filesystem in memory for the reason given
in [zx-next-emulator.md](zx-next-emulator.md#building-the-card).

## The size in the dialog

TSConf runs at **760x576**: `nativeResolution` launches MAME with it and
`mame-canvas.ts` nudges the canvas back to it once the runtime is up.

MAME measures the canvas when SDL brings its window up and then leaves it alone,
so it draws into whatever shape it found — and in Chrome the dialog's box is
often not laid out yet by then. A resize event with the machine's own size sets
it right. `settleCanvasSize()` takes the size rather than knowing one, because
the engines share a single global `Module` and each machine has its own (the
Next 720x576, the SAM 576x550).

## Resolution

`EmulatorResolverService` matches `tsconf` on hardware alone, so a release
holding none of `spg`, `img`, `trd`, `scl` resolves to no emulator and no play
button — see [release.md](release.md).

## Limits

- **Only ZIP archives are unpacked in the browser.** A release packed in
  anything else — RAR, 7z, gzip, bzip2 — is refused with a message telling the
  person to download it instead.
- **The card is rebuilt per play**, so a saved game does not survive the dialog
  being closed.
- Screenshot capture (F2) and the dialog's restart button are not wired for this
  engine; MAME resets on its own F3.
