import {EmulatorEngine, EmulatorType} from './emulator-engine';

const SCRIPT_URL = '/libs/zx81/zx81_emu.js';
const ROM_URL = '/libs/zx81/roms/zx81.rom';

interface Zx81EmulatorUI {
  stop(): void;
}

interface Zx81Globals {
  ZX81EmulatorUI?: new (status: HTMLElement | null, canvas: HTMLCanvasElement, url: string) => Zx81EmulatorUI;
  zx81opts?: {rom?: string};
}

export class Zx81Engine implements EmulatorEngine {
  readonly type: EmulatorType = 'zx81';

  private emulatorUi: Zx81EmulatorUI | null = null;
  private scriptInjected = false;

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
        this.emulatorUi = new globals.ZX81EmulatorUI(null, canvas, fileUrl);
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

  setFullscreen(): void {
    // ZX81 emulator does not expose a fullscreen API.
  }

  destroy(): void {
    this.emulatorUi?.stop();
    this.emulatorUi = null;
  }
}
