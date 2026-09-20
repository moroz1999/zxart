# MAME (WebAssembly) — the TSConf-family, SAM Coupé and ZX Spectrum Next runtime

`mame.js` + `mame.wasm` are MAME **0.282** compiled with Emscripten, carrying
the Sinclair drivers this site plays: `tbblue` (ZX Spectrum Next, the full
`specnext` device set — copper, DMA, sprites, Layer 2, tiles, lores, DivMMC),
`samcoupe`, and the rest of the family. `loader.js` and `browserfs.min.js` are
[Emularity](https://github.com/db48x/emularity), which mounts the files MAME
opens into an in-memory filesystem and drives the Emscripten module.

`roms/` holds the system ROMs each driver asks for, `next/` the NextZXOS
content the Next boots from, `build/` what that content is generated from, and
`software/next.img` a ready-made SD card for the standalone pages here.

## Rebuilding MAME

```
git clone https://github.com/emscripten-core/emsdk.git
cd emsdk && source ./emsdk_env.sh
./emsdk install 3.1.25 && ./emsdk activate 3.1.25

cd ..
git clone --branch mame0282 --depth 1 https://github.com/mamedev/mame.git
cd mame
emmake make -j6 SOURCES=sinclair/specnext.cpp,sinclair/spec128.cpp,sinclair/spectrum.cpp,\
sinclair/tsconf.cpp,sinclair/scorpion.cpp,sinclair/sprinter.cpp,samcoupe/samcoupe.cpp
```

That list is readable back out of the binary: `mame.wasm` keeps the source path
of every file it was compiled from.

MAME is **GPL-2.0-or-later**: the built `mame.wasm` here is covered by it, and
the corresponding source is the upstream tree named above. Background on the
Emscripten target:
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

## The pages here

`next.html` and `samcoupe.html` boot a machine straight from this directory,
outside the site's Angular app. `next-bench.html` runs the Next with video,
sound and throttling off and reports the average speed MAME measures for
itself; `?seconds=N` sets how long it runs.
