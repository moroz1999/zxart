import {EmscriptenModule, EmulatorEngine, EmulatorType} from './emulator-engine';

const SCRIPT_URL = '/libs/us/unreal_speccy_portable.js';
const LIB_BASE = '/libs/us/';

export class UspEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'usp';

  private fileUrl: string | null = null;
  private canvas: HTMLCanvasElement | null = null;
  private scriptInjected = false;

  start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void> {
    this.canvas = canvas;
    this.fileUrl = fileUrl;

    if (this.scriptInjected && window.Module) {
      this.onWasmReady();
      return Promise.resolve();
    }

    return new Promise<void>((resolve, reject) => {
      this.installModule(() => {
        this.onWasmReady();
        resolve();
      });
      const script = document.createElement('script');
      script.src = SCRIPT_URL;
      script.onerror = () => reject(new Error(`Failed to load ${SCRIPT_URL}`));
      document.body.appendChild(script);
      this.scriptInjected = true;
    });
  }

  setFullscreen(): void {
    this.callCommand('full screen=on');
  }

  destroy(): void {
    this.canvas = null;
    this.fileUrl = null;
  }

  private installModule(onReady: () => void): void {
    const config = {
      canvas: this.canvas ?? undefined,
      locateFile: (file: string) => LIB_BASE + file,
      onReady,
    };
    window.Module = config as EmscriptenModule;
  }

  private onWasmReady(): void {
    this.openCurrentFile();
    this.callCommand('joystick=cursor');
    this.callCommand('filtering=off');
  }

  private openCurrentFile(): void {
    if (!this.fileUrl || !window.Module?.ccall) {
      return;
    }
    window.Module.ccall('OpenFile', null, ['string'], [this.fileUrl]);
  }

  private callCommand(command: string): void {
    if (!window.Module?.ccall) {
      return;
    }
    window.Module.ccall('OnCommand', null, ['string'], [command]);
  }
}
