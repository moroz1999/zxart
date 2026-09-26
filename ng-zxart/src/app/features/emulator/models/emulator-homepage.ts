import {EmulatorType} from '../engines/emulator-engine';

/** Name and home page of the emulator behind an emulator type, credited in the dialog. */
export interface EmulatorHomepage {
  readonly name: string;
  readonly url: string;
}

const MAME: EmulatorHomepage = {
  name: 'MAME',
  url: 'https://www.mamedev.org/',
};

const JSSPECCY: EmulatorHomepage = {
  name: 'JSSpeccy 3',
  url: 'https://github.com/dtz-labs/jsspeccy3',
};

/** `null` until the project the emulator comes from is known. */
export const EMULATOR_HOMEPAGES: Record<EmulatorType, EmulatorHomepage | null> = {
  usp: {name: 'Unreal Speccy Portable', url: 'https://github.com/djdron/unrealspeccyp'},
  zx81: {name: 'JtyOne', url: 'https://github.com/hammingweight/zx81-javascript-emulator'},
  tsconf: MAME,
  samcoupe: MAME,
  zxnext: MAME,
  scorpion: MAME,
  atm: MAME,
  profi: MAME,
  pentevo: MAME,
  sprinter: MAME,
  timex2048: JSSPECCY,
  timex2068: JSSPECCY,
};
