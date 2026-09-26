/**
 * The machines the MAME runtime is launched as.
 *
 * One WebAssembly core carries every driver here, so a machine is nothing but
 * the driver name, the files it opens and the switches it is started with —
 * which is what a profile holds. The values come from the reference pages in
 * `htdocs/libs/mame/`, one per machine, which boot the same machine with
 * nothing of the site around it.
 */

/** A file the runtime directory serves, and where the machine looks for it. */
export interface MachineFile {
  /** Path under `htdocs/libs/mame/`. */
  readonly source: string;
  /** Path MAME opens it by, relative to its working directory. */
  readonly target: string;
}

export interface MameMachine {
  /** MAME's own name for the machine. */
  readonly driver: string;
  /**
   * The size MAME is launched at, as whole pixels of the machine's picture.
   * It is only the opening bid: once the core is up the engine asks it for
   * the visible area it actually draws and settles the canvas on that.
   */
  readonly width: number;
  readonly height: number;
  /** ROM archives under `roms/`, by file name. */
  readonly roms: readonly string[];
  /** Fixed files beyond the ROMs: saved NVRAM, MAME configuration. */
  readonly files?: readonly MachineFile[];
  /** Which directory MAME reads its configuration from. */
  readonly cfgDirectory?: string;
  /** Which directory MAME reads NVRAM from, for machines with a saved state. */
  readonly nvramDirectory?: string;
  /** Switches beyond the ones every machine here is started with. */
  readonly args: readonly string[];
  /**
   * How this machine takes a second AY, for a release that wants TurboSound.
   * Without it the machine keeps the one AY it was born with.
   */
  readonly turboSoundArgs?: readonly string[];
  /**
   * How this machine takes a NeoGS, for a release with a General Sound track.
   * Absent on the ATM Turbo, which has no ZX Bus to put one on.
   *
   * The machines that carry one as standard have it in `args` instead, because
   * what device number their SD card gets depends on it being there.
   */
  readonly neoGsArgs?: readonly string[];
}

/** The Beta Disk ROMs every TR-DOS machine here asks for. */
const BETADISK = 'betadisk.zip';
/** The NeoGS — General Sound — as the ZX Bus card the machines carry it on. */
const NEOGS = 'zxbus_neogs.zip';
/** The keyboard the TSConf and the Sprinter are wired to. */
const KEYBOARD = 'kb_ms_natural.zip';

/**
 * The switches every machine is launched with, as the reference pages in
 * `htdocs/libs/mame/` carry them.
 *
 * `bgfx` with the `unfiltered` chain draws whole pixels, and
 * `-nounevenstretch` keeps them square; without both the picture crawls as the
 * dialog is resized.
 */
export const MAME_COMMON_ARGS: readonly string[] = [
  '-window',
  '-nounevenstretch',
  '-video', 'bgfx',
  '-bgfx_screen_chains', 'unfiltered',
];

export const ZXNEXT_MACHINE: MameMachine = {
  driver: 'tbblue',
  // The Next's picture is 360x288 at its widest, and MAME is given whole
  // pixels of it at the 2:1 the machine's own aspect asks for.
  width: 720,
  height: 576,
  roms: ['tbblue.zip'],
  // The core's stock boot ROM is 3.02.04; 3.01.00 is the one the NextZXOS
  // distribution pinned in `next/` boots from.
  args: ['-bios', 'v30100', '-aspect', '2:1'],
};

export const SAMCOUPE_MACHINE: MameMachine = {
  driver: 'samcoupe',
  width: 576,
  height: 550,
  roms: ['samcoupe.zip'],
  args: ['-mouseport', 'mouse', '-ab', '........................boot\\n'],
};

export const TSCONF_MACHINE: MameMachine = {
  driver: 'tsconf2',
  width: 720,
  height: 576,
  roms: ['tsconf.zip', KEYBOARD, NEOGS, BETADISK],
  files: [{source: 'cfg/tsconf2.cfg', target: 'cfg/tsconf2.cfg'}],
  cfgDirectory: 'cfg',
  // The NeoGS is standard here and stays plugged in whatever the release
  // wants: the TSConf's own SD card is `hard1` only because it is there.
  args: ['-zxbus1', 'neogs'],
  turboSoundArgs: ['-ay_slot', 'ay_turbosound'],
};

export const SCORPION_MACHINE: MameMachine = {
  driver: 'scorpiongmx',
  width: 704,
  height: 592,
  roms: ['scorpiongmx.zip', NEOGS, BETADISK],
  // The SMUC's saved clock and settings, so the machine comes up configured
  // rather than at its setup screen.
  files: [
    {source: 'nvram/scorpiongmx/zxbus_1_smuc_eeprom', target: 'nvram/scorpiongmx/zxbus_1_smuc_eeprom'},
    {source: 'nvram/scorpiongmx/zxbus_1_smuc_rtc', target: 'nvram/scorpiongmx/zxbus_1_smuc_rtc'},
  ],
  // The NeoGS is standard kit on a GMX, so it stays plugged in.
  args: ['-zxbus:1', 'smuc', '-zxbus:2', 'neogs'],
  turboSoundArgs: ['-ay_slot', 'ay_turbosound'],
};

export const ATM_MACHINE: MameMachine = {
  driver: 'atmtb2plus',
  width: 704,
  height: 592,
  roms: ['atmtb2plus.zip', BETADISK],
  // Dual eXtra: the newest of the four BIOS sets the runtime carries, and the
  // only one with TR-DOS 5.04R. No NeoGS: the ATM has no ZX Bus, which is why
  // an ATM release wanting one is sent to the ZX Evolution instead.
  args: ['-bios', 'v1.37'],
  turboSoundArgs: ['-ay_slot', 'ay_turbosound'],
};

export const PROFI_MACHINE: MameMachine = {
  driver: 'profi',
  width: 704,
  height: 592,
  roms: ['profi.zip', NEOGS, BETADISK],
  args: [],
  turboSoundArgs: ['-ay_slot', 'ay_turbosound'],
  neoGsArgs: ['-zxbus:1', 'neogs'],
};

export const PENTEVO_MACHINE: MameMachine = {
  driver: 'pentevo',
  width: 720,
  height: 576,
  roms: ['pentevo.zip', NEOGS, BETADISK],
  args: [],
  turboSoundArgs: ['-ay_slot', 'ay_turbosound'],
  neoGsArgs: ['-zxbus1', 'neogs'],
};

export const SPRINTER_MACHINE: MameMachine = {
  driver: 'sprinter',
  width: 736,
  height: 576,
  roms: ['sprinter.zip', KEYBOARD, NEOGS, BETADISK],
  files: [
    {source: 'cfg/sprinter.cfg', target: 'cfg/sprinter.cfg'},
    {source: 'nvram/sprinter_6/rtc', target: 'nvram/sprinter_6/rtc'},
  ],
  cfgDirectory: 'cfg',
  // The Sprinter reaches the ZX Bus — and so the NeoGS — over an ISA adapter
  // rather than carrying the bus itself. Its keyboard ROM is the one the
  // runtime has, not the Sprinter's own variant.
  args: [
    '-bios', 'v3.06',
    '-kbd', 'ms_naturl,bios=orig',
  ],
  neoGsArgs: ['-isa0', 'zxbus_adapter', '-isa0:zxbus_adapter:card', 'neogs'],
};
