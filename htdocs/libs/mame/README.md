# MAME (WebAssembly) — the machines the site plays

`mame.js` + `mame.wasm` are MAME compiled with Emscripten, carrying every
Sinclair-family driver this site runs as one core:

| Machine | Driver | Notes |
| --- | --- | --- |
| ZX Spectrum Next | `tbblue` | the full `specnext` device set — copper, DMA, sprites, Layer 2, tiles, lores, DivMMC |
| TSConf | `tsconf2` | TS-Configuration 2, with the NeoGS on the ZX Bus |
| Scorpion GMX | `scorpiongmx` | SMUC and NeoGS on the ZX Bus |
| ATM Turbo 2+ | `atmtb2plus` | the one machine here with no ZX Bus, so no NeoGS |
| Profi | `profi` | NeoGS on the ZX Bus |
| ZX Evolution: BaseConf | `pentevo` | NeoGS on the ZX Bus |
| Sprinter Sp2000 | `sprinter` | NeoGS over the ISA-to-ZXBUS adapter |
| SAM Coupé | `samcoupe` | |

The core carries the rest of the family too — `spectrum`, `spec128`, the `+2`,
`+2a` and `+3`, `atm`, `atmtb2`, `scorpio`, `scorpiontb`, `byte`, the Didaktik
and HC clones, `tk90x` — which nothing here launches; the site plays a plain
Spectrum release on Unreal Speccy Portable.

`mame-loader.js` fetches the files a machine opens, puts them in the Emscripten
filesystem and starts the core. It keeps a versioned IndexedDB cache of
everything it downloads, so the ROM archives are paid for once per visitor
rather than once per play. **Two changes of ours are in it**, and both have to
be carried over when a newer loader arrives:

- A file handed over as a `blob:` URL — a card built for one release, an
  unpacked disk — is left out of that cache: its name is one nothing can ever
  ask for again, so caching it would fill the store a play at a time.
- `opts.emulatorJS` names where `mame.js` is. The loader's own default is the
  bare name, which resolves against the page — right for the reference pages
  here, wrong for the site, whose emulator opens over a route like `/prod/123`.

`theme-simple.js` is the interface the reference pages here are built with; the
site's own dialog uses none of it.

`roms/` holds the ROM archives each driver asks for, `cfg/` and `nvram/` the
saved MAME configuration and machine state some of them come up in, `next/` the
NextZXOS content the Next boots from, `build/` what that content is generated
from, and `software/` whatever you drop there for the reference pages.

`.wasm` is in the `mod_deflate` list in `htdocs/.htaccess` — the core is 34 MB
raw and about 7 MB compressed.

## The reference pages

`index.html` lists one page per machine. Each boots that machine from
`software/` with nothing of the site around it, and `?file=NAME` starts another
file from the same folder. They are where the engine profiles in
`ng-zxart/src/app/features/emulator/engines/mame-machine.ts` come from: a
machine that misbehaves in the dialog is worth trying here first, because the
page has no release staging, no SD card writer and no Angular in the way.

`software/` is not in git. `next.img` is built by
`build/make-nextzxos-assets.sh`; everything else is a release you put there
yourself.

## Which BIOS each machine boots

Three machines are pinned to a BIOS that is not the driver's default, because
the ROM sets here do not carry the default one:

- `tbblue` — `-bios v30100`, the boot ROM the pinned NextZXOS distribution goes
  with. The core's own default is 3.02.04.
- `atmtb2plus` — `-bios v1.37`, Dual eXtra, the newest of the four sets here and
  the only one with TR-DOS 5.04R. The `gluk` and `gluk2` sets are not here.
- `sprinter` — `-kbd ms_naturl,bios=orig` for the keyboard, whose own default
  wants a Sprinter-specific ROM that is not here.

## Where the build comes from

The core is not built here: it arrives as a drop from its author, as
`mame.js`, `mame.wasm`, `mame-loader.js` and `theme-simple.js` together with
the reference pages the ones here are derived from.

It is not stock MAME either. `mame.wasm` keeps the source path of every file it
was compiled from, so what went into it is readable back out of the binary:

```
strings mame.wasm | grep -oE "src/mame/[a-z0-9_/]+\\.cpp" | sort -u
```

which gives `sinclair/atm.cpp`, `sinclair/beta_m.cpp`, `sinclair/byte.cpp`,
`sinclair/scorpion.cpp`, `sinclair/spec128.cpp`, `sinclair/spectrum.cpp`,
`sinclair/specpls3.cpp`, `sinclair/sprinter.cpp`, `sinclair/evo/*.cpp` (TSConf
and BaseConf), `sinclair/next/specnext*.cpp` and `samcoupe/samcoupe.cpp`. The
`evo/` and `next/` folders are the author's own layout, and `tsconf2` and
`scorpiongmx` are his drivers — this is a fork, not the upstream tree.

MAME is **GPL-2.0-or-later** and the built `mame.wasm` here is covered by it;
ask the author for the matching source. Background on the Emscripten target:
<https://8bitworkshop.com/docs/posts/2020/compiling-emulators-to-webassembly-without-emscripten.html>.

## Rebuilding the NextZXOS assets

`next/nextzxos.zip` is the NextZXOS system tree the browser builds an SD card
around, and `software/next.img` is that tree on a finished card together with
the releases listed in `build/releases.txt`. Both come out of one script:

```
build/make-nextzxos-assets.sh [path/to/tbblue.mmc.zip]
```

It reads `build/tbblue.mmc.zip`, the pinned NextZXOS card (another path can be
given as the argument). That card was built from the **official SpecNext
distribution**, filtered down to the freely redistributable system tree:

    https://www.specnext.com/distro/24.11/sn-emulator-24.11.zip
    sha256 b2b0cbfb421acba2dcd00b081e647d7ead7bd0bc35952f0fdcafe1581321b516
    51829771 bytes, published 2024-12-05

`machines/next/config.ini` in it is seeded so the Next boots straight to the
NextZXOS menu instead of the interactive video-mode wizard.

## Licensing of `next/`

`next/` is **not** GPL. The NextZXOS system tree is copyright Garry Lancaster /
SpecNext Ltd with portions (c) Amstrad plc, carried under The Next License:
cost-free distribution only, copyright notices retained, and the licence texts
travel with the copy — they are in `next/licenses/`, served alongside. Only the
system tree is here; the distribution's per-title items (games, demos, tools,
the QL core) are licensed by their own authors and are not distributed.
