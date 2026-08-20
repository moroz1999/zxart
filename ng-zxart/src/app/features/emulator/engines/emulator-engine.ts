export type EmulatorType = 'usp' | 'zx81' | 'tsconf' | 'samcoupe' | 'zxnext' | 'timex2048' | 'timex2068';

export type ScreenshotFormat = 'standard' | 'gigascreen';

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
   */
  start(canvas: HTMLCanvasElement, fileUrl: string, container: HTMLElement): Promise<void>;

  setFullscreen(): void;

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
