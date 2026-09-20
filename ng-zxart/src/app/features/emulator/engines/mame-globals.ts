export interface MameLoaderStatic {
  driver(name: string): unknown;
  nativeResolution(width: number, height: number): unknown;
  emulatorJS(path: string): unknown;
  emulatorWASM(path: string): unknown;
  mountFile(target: string, source: unknown): unknown;
  fetchFile(label: string, url: string): unknown;
  /** Mounts bytes the page already holds, in place of a file to download. */
  localFile(label: string, data: Uint8Array): unknown;
  peripheral(name: string, value: string): unknown;
  /** Names the IndexedDB store the loader mirrors mounted files into. */
  fileSystemKey(key: string): unknown;
  /** Multiplies the native resolution to size the canvas. */
  scale(scale: number): unknown;
  extraArgs(args: string[]): unknown;
}

export interface MameLoaderConstructor extends MameLoaderStatic {
  new(...args: unknown[]): unknown;
}

export interface MameEmulatorInstance {
  start(opts: {waitAfterDownloading: boolean}): void;
  requestFullScreen(): void;
  stop(): void;
  mute(): void;
  unmute(): void;
}

export type MameEmulatorConstructor = new (
  canvas: HTMLCanvasElement,
  opts: unknown,
  loader: unknown,
) => MameEmulatorInstance;

export interface MameGlobals {
  MAMELoader?: MameLoaderConstructor;
  Emulator?: MameEmulatorConstructor;
}
