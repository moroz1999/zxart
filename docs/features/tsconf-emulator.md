# TSConf emulator — implementation

Domain rules: [../domain/release.md](../domain/release.md#running-in-the-browser).

TSConf runs on MAME's `tsconf2` driver — TS-Configuration 2 — on the shared
core described in [mame-machines.md](mame-machines.md). Like the Next it is
handed the release **whole** rather than one file picked out of it, because a
TSConf program opens its data files off the SD card while it runs.

## Runtime

The machine's own files in `htdocs/libs/mame/` are the ROMs its devices ask for
(`tsconf.zip`, `betadisk.zip`, `kb_ms_natural.zip`, `zxbus_neogs.zip`), the
MAME config that switches the second AY on (`cfg/tsconf2.cfg`) and two saved
TS-BIOS configurations (`nvram/tsconf2/glukrs_trd`, `nvram/tsconf2/glukrs_sd`)
that differ in one setting: which device the machine boots from. MAME reads
NVRAM from one directory, so whichever configuration a play needs is mounted as
`glukrs` under the directory that play comes up in — `nvramtrd` or `nvramsd`.

The NeoGS is plugged into `zxbus1` and `-ay_slot ay_turbosound` switches the
second AY on. The NeoGS brings an SD card of its own, so the machine has two:
`hard1` is the one TS-BIOS boots from and the one a release is played on.

**The saved configurations belong to this core.** MAME's Mr Gluk store is
written differently than it was — the padding between its registers changed —
so a configuration saved by an older build reads as nonsense here and TS-BIOS
comes up in its Setup Utility instead of booting. A machine sitting in Setup is
what a stale NVRAM file looks like.

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
| `img` | `hard1`, the SD card — the release ships a whole card | the SD |

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
`/ANOTHER/bank01` through `/ANOTHER/bank0d`). So an SPG launch also gets a card
on `hard1`, built by `fat-card.ts` — the same FAT32 writer the Next's card
comes from — holding **every file of the release at the path it was published
under**, which is where the release's own instructions say to copy it.

A release that ships an SD card image of its own is mounted as that card
instead. A TR-DOS release gets no card at all: everything it loads is on its
disk.

Nothing is generated on the server and nothing survives the dialog closing.

## The size in the dialog

TSConf is launched at **720x576** and then settled on the visible area the core
reports; how that works, and why, is in
[mame-machines.md](mame-machines.md#the-size-in-the-dialog).

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
- Screenshot capture (F2) is not wired for this engine.
