export type EmulatorType = 'usp' | 'zx81' | 'tsconf' | 'samcoupe' | 'zxnext' | 'timex2048' | 'timex2068';

export type ScreenshotFormat = 'standard' | 'gigascreen';

/** Extras a release can carry into an engine beyond the file to load. */
export interface EmulatorStartOptions {
  /**
   * Path inside the release file of the program to start, for engines that
   * mount the whole release rather than loading one file out of it.
   */
  launchFilePath?: string;

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
