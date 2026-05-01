export interface MameLoaderStatic {
  driver(name: string): unknown;
  nativeResolution(width: number, height: number): unknown;
  emulatorJS(path: string): unknown;
  emulatorWASM(path: string): unknown;
  mountFile(target: string, source: unknown): unknown;
  fetchFile(label: string, url: string): unknown;
  peripheral(name: string, value: string): unknown;
  extraArgs(args: string[]): unknown;
}

export interface MameLoaderConstructor extends MameLoaderStatic {
  new(...args: unknown[]): unknown;
}

export interface MameEmulatorInstance {
  start(opts: {waitAfterDownloading: boolean}): void;
  requestFullScreen(): void;
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

export function loadScriptOnce(state: {injected: boolean}, src: string): Promise<void> {
  if (state.injected) {
    return Promise.resolve();
  }
  return new Promise<void>((resolve, reject) => {
    const script = document.createElement('script');
    script.src = src;
    script.onload = () => {
      state.injected = true;
      resolve();
    };
    script.onerror = () => reject(new Error(`Failed to load ${src}`));
    document.body.appendChild(script);
  });
}
