import {EmulatorEngine, EmulatorType, ScreenshotFormat} from './emulator-engine';

const SCRIPT_URL = '/libs/zx81/zx81_emu.js';
const ROM_URL = '/libs/zx81/roms/zx81.rom';

interface Zx81EmulatorUI {
  stop(): void;
  jtyOne: {getDisplayFile(): number[]};
}

interface Zx81Globals {
  ZX81EmulatorUI?: new (status: HTMLElement | null, canvas: HTMLCanvasElement, url: string) => Zx81EmulatorUI;
  zx81opts?: {rom?: string};
}

export class Zx81Engine implements EmulatorEngine {
  readonly type: EmulatorType = 'zx81';

  private emulatorUi: Zx81EmulatorUI | null = null;
  private canvas: HTMLCanvasElement | null = null;
  private scriptInjected = false;
  private readonly visibilityHandler = () => {
    if (document.hidden) {
      this.emulatorUi?.stop();
    }
  };

  start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void> {
    return new Promise<void>((resolve, reject) => {
      const startEmulator = () => {
        const globals = window as unknown as Zx81Globals;
        if (!globals.ZX81EmulatorUI) {
          reject(new Error('ZX81EmulatorUI global is not available'));
          return;
        }
        globals.zx81opts = {...(globals.zx81opts ?? {}), rom: ROM_URL};
        if (this.emulatorUi) {
          this.emulatorUi.stop();
        }
        this.canvas = canvas;
        this.emulatorUi = new globals.ZX81EmulatorUI(document.createElement('div'), canvas, fileUrl);
        document.addEventListener('visibilitychange', this.visibilityHandler);
        resolve();
      };

      if (this.scriptInjected) {
        startEmulator();
        return;
      }

      const script = document.createElement('script');
      script.src = SCRIPT_URL;
      script.onload = () => {
        this.scriptInjected = true;
        startEmulator();
      };
      script.onerror = () => reject(new Error(`Failed to load ${SCRIPT_URL}`));
      document.body.appendChild(script);
    });
  }

  captureScreenshot(_format: ScreenshotFormat): Promise<Blob | null> {
    if (!this.emulatorUi) {
      return Promise.resolve(null);
    }
    const ROW_WIDTH = 32;
    const ROW_COUNT = 24;
    const NEWLINE = 118;
    const output = new Uint8Array(ROW_WIDTH * ROW_COUNT); // initialised to 0 (ZX81 space)
    const raw = this.emulatorUi.jtyOne.getDisplayFile();
    let row = 0;
    let col = 0;
    for (const byte of raw) {
      if (row >= ROW_COUNT) {
        break;
      }
      if (byte === NEWLINE) {
        row++;
        col = 0;
      } else if (col < ROW_WIDTH) {
        output[row * ROW_WIDTH + col] = byte;
        col++;
      }
    }
    return Promise.resolve(new Blob([output]));
  }

  setFullscreen(): void {
    void this.canvas?.requestFullscreen();
  }

  destroy(): void {
    document.removeEventListener('visibilitychange', this.visibilityHandler);
    this.emulatorUi?.stop();
    this.emulatorUi = null;
    this.canvas = null;
  }
}
