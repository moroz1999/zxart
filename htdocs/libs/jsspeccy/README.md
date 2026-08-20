# JSSpeccy 3

Browser emulator used for the Timex TC2048 and TC2068 machines.

- Source: https://github.com/dtz-labs/jsspeccy3 (fork of https://github.com/gasman/jsspeccy3 adding the Timex machines)
- Version: v3.2.0-timex.2, the `jsspeccy-dist.tar.gz` release archive
- Licence: GPL-3.0, see COPYING. The bundled ROM images are not covered by it.

To update, download a newer release archive and replace the contents of this
directory with its `jsspeccy` folder. Keep `jsspeccy.js`, `jsspeccy-worker.js`,
the `.wasm` core and the `roms`/`tapeloaders` subdirectories together — the
worker and the ROMs are resolved relative to `jsspeccy.js`.
