# MAME machines — implementation

Domain rules: [../domain/release.md](../domain/release.md#running-in-the-browser).

Everything the site plays on MAME runs on **one WebAssembly core**, in
`htdocs/libs/mame/`. Its `README.md` says where the build comes from, which
machines are in it, which BIOS each is pinned to and how the NextZXOS content
beside it is generated; read it before changing anything in there.

| Emulator id | Driver | Machine |
| --- | --- | --- |
| `zxnext` | `tbblue` | ZX Spectrum Next — see [zx-next-emulator.md](zx-next-emulator.md) |
| `tsconf` | `tsconf2` | TSConf — see [tsconf-emulator.md](tsconf-emulator.md) |
| `samcoupe` | `samcoupe` | SAM Coupé |
| `scorpion` | `scorpiongmx` | Scorpion GMX, with SMUC and NeoGS |
| `atm` | `atmtb2plus` | ATM Turbo 2+ |
| `profi` | `profi` | Profi, with NeoGS |
| `pentevo` | `pentevo` | ZX Evolution: BaseConf, with NeoGS |
| `sprinter` | `sprinter` | Sprinter Sp2000, with NeoGS |

One core is the point: a visitor who plays a Scorpion release and then a Next
one downloads 34 MB once, and the loader keeps it and the ROM archives in
IndexedDB, so the second play costs nothing.

## What a machine is

`mame-machine.ts` holds one profile per machine — driver, the size to launch
at, the ROM archives, the fixed files it opens and the switches beyond the ones
every machine gets. Nothing else in the frontend knows a driver name.

The reference pages in `htdocs/libs/mame/` are the other half of that: one page
per machine, booting it from `software/` with nothing of the site around it,
and the profiles are kept in step with them. A machine that misbehaves in the
dialog is tried there first.

`MAME_COMMON_ARGS` is what every machine is started with: `bgfx` with the
`unfiltered` chain and `-nounevenstretch`, which together draw whole square
pixels. It is kept to exactly what the reference pages carry, because those are
what each machine is known to boot with.

**A machine that opens on a boot menu waits for the person.** The Scorpion and
the ATM Turbo come up on a device list with TR-DOS already under the cursor, so
Enter starts the disk; the ZX Evolution opens the EVO Reset Service, where `2`
is TR-DOS boot. Nothing presses these: MAME's `-autoboot_command` types through
its natural keyboard, and a menu living in ROM reads the key matrix directly,
so none of it arrives.

## What an engine does

`mame.engine.ts` is the base every MAME engine extends. It starts the core,
mutes and pauses with the tab, goes full screen, resets, and settles the
canvas. An engine of its own supplies only the profile and a `prepare()` that
says what this play hands the machine: files to mount, the switches naming
them, and which saved NVRAM directory to come up in.

`beta-disk.engine.ts` is one engine for five machines. A Scorpion, an ATM
Turbo, a Profi, a ZX Evolution and a Sprinter differ only in their profile, and
all of them boot TR-DOS off `flop1`, so what is left to write is which device
the launch file goes on — and that is decided by its extension, not by the
machine.

## Handing the machine a file

The loader fetches what it mounts, so bytes the page already holds — a disk
unpacked from the release, an SD card just built — are named with a `blob:`
URL and revoked once the machine has read them. **The loader leaves `blob:`
URLs out of its cache**, which is a change of ours in `mame-loader.js`: such a
name is one nothing can ever ask for again, and caching it would fill the
store a card at a time. The README says to keep it.

Whatever the release calls the file, it is mounted as `release.trd`,
`release.spg` or `release.img`: every device here picks its format from the
extension, and a published name may hold spaces, brackets or a second dot.

**MAME's floppy cannot read an SCL.** `scl-to-trd.ts` lays the archive out as
the TR-DOS disk it holds — the catalogue, then the files back to back from
track 1 — and that TRD is what is mounted.

**A TRD trimmed to the sectors it uses is refused too.** MAME works a disk's
geometry out from the file size, so a published image that stops after its last
file answers `Unable to identify image file format`. `trd-image.ts` pads it back
out to the disk its own volume sector says it is.

## The size in the dialog

A machine is launched at the size in its profile and the canvas is set to it
**before** the core starts: MAME measures the canvas when SDL brings its window
up and then draws into whatever shape it found, and in Chrome the dialog's box
is often not laid out yet at that moment.

The profile's size is only the opening bid. Once the core is up the engine asks
it for the visible area it actually draws — `visibleArea()` — and resizes the
backing to exactly twice that. Doubling is what keeps pixels whole: a size that
is not a multiple of the machine's own makes the picture crawl.

## Sound

**The release decides what the machine is fitted with.** Its hardware set
travels into the engine as `hardware`, and the profile says how that machine
takes each card:

| The release asks for | Fitted |
| --- | --- |
| `ts` | a second AY, `-ay_slot ay_turbosound` |
| `gs` or `ngs` | a NeoGS — one card answers for both codes |

A release naming neither keeps the single AY the machine was born with.

Two machines carry a NeoGS whatever the release wants, and that is in their
`args` rather than in `neoGsArgs`: the **Scorpion GMX**, where it is standard
kit, and the **TSConf**, where the card has to be there because the machine's
own SD card is `hard1` only while it is. The **ATM Turbo** cannot take one at all:
it has no ZX Bus, which is why an ATM release wanting General Sound is sent to
the ZX Evolution instead — the two are compatible and that one has the bus. See
[release.md](release.md#emulator-launch-capability).

## Limits

- **Only ZIP archives are unpacked in the browser.** `release-files.ts`
  recognises RAR, 7z, gzip and bzip2 and refuses them by name rather than
  staging one unreadable blob under the program's name.
- **Nothing survives the dialog closing.** Closing it reloads the page, which
  is the only thing that unloads an Emscripten emulator; see
  `EmulatorModalService`.
- Screenshot capture (F2) is not wired for these engines; MAME takes its own
  with F12 once its menu key is pressed.
- The Sprinter boots its operating system off a hard disk image that is not
  distributed here, so it comes up without one until `software/sp_hdd_sys.chd`
  is put in place.
