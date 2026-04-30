export type EmulatorType = 'usp' | 'zx81' | 'tsconf' | 'samcoupe' | 'zxnext';

export type ScreenshotFormat = 'standard' | 'gigascreen';

export interface EmulatorEngine {
  readonly type: EmulatorType;

  start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void>;

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
}

declare global {
  interface Window {
    Module?: EmscriptenModule;
  }
}
