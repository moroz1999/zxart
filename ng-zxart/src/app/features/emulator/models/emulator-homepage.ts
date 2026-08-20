import {EmulatorType} from '../engines/emulator-engine';

/** Name and home page of the emulator behind an emulator type, credited in the dialog. */
export interface EmulatorHomepage {
  readonly name: string;
  readonly url: string;
}

const JSSPECCY: EmulatorHomepage = {
  name: 'JSSpeccy 3',
  url: 'https://github.com/dtz-labs/jsspeccy3',
};

/** `null` until the project the emulator comes from is known. */
export const EMULATOR_HOMEPAGES: Record<EmulatorType, EmulatorHomepage | null> = {
  usp: {name: 'Unreal Speccy Portable', url: 'https://github.com/djdron/unrealspeccyp'},
  zx81: {name: 'JtyOne', url: 'https://github.com/hammingweight/zx81-javascript-emulator'},
  tsconf: null,
  samcoupe: null,
  zxnext: null,
  timex2048: JSSPECCY,
  timex2068: JSSPECCY,
};
