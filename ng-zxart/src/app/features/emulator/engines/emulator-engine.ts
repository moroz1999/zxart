export type EmulatorType =
  | 'usp'
  | 'zx81'
  | 'tsconf'
  | 'samcoupe'
  | 'zxnext'
  | 'scorpion'
  | 'atm'
  | 'profi'
  | 'pentevo'
  | 'sprinter'
  | 'timex2048'
  | 'timex2068';

/**
 * The emulator ids the frontend can actually start. The backend resolves an
 * emulator per release and the answer travels as a plain string, so this is
 * where it is checked against what is built in — one list, because a machine
 * that is added has to become playable everywhere at once.
 */
export const SUPPORTED_EMULATOR_TYPES: ReadonlyArray<EmulatorType> = [
  'usp', 'zx81', 'tsconf', 'samcoupe', 'zxnext',
  'scorpion', 'atm', 'profi', 'pentevo', 'sprinter',
  'timex2048', 'timex2068',
];

/** Narrows what the backend said to an emulator this build can start. */
export function toSupportedEmulatorType(type: string | null | undefined): EmulatorType | null {
  return type && SUPPORTED_EMULATOR_TYPES.includes(type as EmulatorType) ? (type as EmulatorType) : null;
}

export type ScreenshotFormat = 'standard' | 'gigascreen';

/** Extras a release can carry into an engine beyond the file to load. */
export interface EmulatorStartOptions {
  /**
   * Path inside the release file of the program to start, for engines that
   * mount the whole release rather than loading one file out of it.
   */
  launchFilePath?: string;

  /**
   * The hardware codes the release needs — its own set gap-filled from its
   * production. A MAME machine takes its sound cards from this: the second AY
   * and the NeoGS are fitted for the release that asks for them.
   */
  hardware?: string[];

  /**
   * Progress of a start that takes a while, as a translation key and its
   * parameters, for the dialog to show in place of a bare "loading".
   */
  onStatus?: (key: string, params?: Record<string, unknown>) => void;
}

export interface EmulatorEngine {
  readonly type: EmulatorType;

  /**
   * True when the engine builds its own interface in the container and leaves
   * the canvas unused, so the dialog knows not to show it.
   */
  readonly rendersOwnUi?: boolean;

  /**
   * @param canvas    the canvas the emulator draws into
   * @param fileUrl   the file to load on startup
   * @param container the box the canvas sits in, for engines mounting their own interface
   * @param options   what the release knows beyond its file URL
   */
  start(
    canvas: HTMLCanvasElement,
    fileUrl: string,
    container: HTMLElement,
    options?: EmulatorStartOptions,
  ): Promise<void>;

  setFullscreen(): void;

  /** Reboot the machine, for engines that can do it without a fresh start. */
  restart?(): void;

  destroy(): void;

  captureScreenshot?(format: ScreenshotFormat): Promise<Blob | null>;
}

export interface EmscriptenModuleConfig {
  canvas?: HTMLCanvasElement;
  locateFile?: (path: string) => string;
  onReady?: () => void;
}

export interface EmscriptenModule extends EmscriptenModuleConfig {
  ccall: (name: string, returnType: string | null, argTypes: string[], args: unknown[]) => unknown;
  setCanvasSize?: (width: number, height: number) => void;
  pauseMainLoop?: () => void;
  resumeMainLoop?: () => void;
  onRuntimeInitialized: () => void;
}

declare global {
  interface Window {
    Module?: EmscriptenModule;
  }
}
