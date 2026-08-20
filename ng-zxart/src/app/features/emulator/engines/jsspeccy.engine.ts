import {EmulatorEngine, EmulatorType} from './emulator-engine';
import {loadScriptOnce} from './load-script';

const SCRIPT_URL = '/libs/jsspeccy/jsspeccy.js';

/** JSSpeccy sizes its window in whole steps of the Spectrum display. */
const STEP_WIDTH = 320;
const STEP_HEIGHT = 240;
/** Three steps make 960x720, the size the other emulators run at. */
const MAX_ZOOM = 3;
/** Room for the menu bar and the toolbar JSSpeccy puts above and below its screen. */
const CHROME_HEIGHT = 64;

export type JsSpeccyEmulatorType = Extract<EmulatorType, 'timex2048' | 'timex2068'>;

/** The machines JSSpeccy is used for here; the value is what its `machine` option expects. */
const MACHINES: Record<JsSpeccyEmulatorType, number> = {
  timex2048: 2048,
  timex2068: 2068,
};

interface JsSpeccyInstance {
  setZoom(zoom: number): void;
  toggleFullscreen(): void;
  onReady(callback: () => void): void;
  exit(): void;
}

interface JsSpeccyGlobals {
  JSSpeccy?: (container: HTMLElement, opts: Record<string, unknown>) => JsSpeccyInstance;
}

const scriptState = {injected: false};

export class JsSpeccyEngine implements EmulatorEngine {
  readonly rendersOwnUi = true;

  private emulator: JsSpeccyInstance | null = null;
  private container: HTMLElement | null = null;
  private readonly resizeHandler = () => this.fitToContainer();

  constructor(readonly type: JsSpeccyEmulatorType) {}

  async start(_canvas: HTMLCanvasElement, fileUrl: string, container: HTMLElement): Promise<void> {
    await loadScriptOnce(scriptState, SCRIPT_URL);
    const globals = window as unknown as JsSpeccyGlobals;
    if (!globals.JSSpeccy) {
      throw new Error('JSSpeccy global is not available');
    }
    this.container = container;
    const emulator = globals.JSSpeccy(container, {
      machine: MACHINES[this.type],
      openUrl: fileUrl,
      autoLoadTapes: true,
      // No autoStart: JSSpeccy creates its AudioContext inside start() and never
      // resumes it, so the sound needs its play button to be pressed by hand.
      sandbox: true,
      zoom: 1,
    });
    this.emulator = emulator;
    window.addEventListener('resize', this.resizeHandler);

    return new Promise<void>(resolve => {
      emulator.onReady(() => {
        this.fitToContainer();
        resolve();
      });
    });
  }

  setFullscreen(): void {
    this.emulator?.toggleFullscreen();
  }

  destroy(): void {
    window.removeEventListener('resize', this.resizeHandler);
    this.emulator?.exit();
    this.emulator = null;
    this.container = null;
  }

  /** JSSpeccy lays itself out in fixed pixels, so the zoom stands in for a fluid size. */
  private fitToContainer(): void {
    // Setting a zoom while fullscreen makes JSSpeccy leave fullscreen again, and
    // entering it fires a resize, so the fullscreen size is left alone. JSSpeccy
    // restores the zoom itself once fullscreen ends.
    if (!this.container || document.fullscreenElement) {
      return;
    }
    const steps = Math.min(
      Math.floor(this.container.clientWidth / STEP_WIDTH),
      Math.floor((this.container.clientHeight - CHROME_HEIGHT) / STEP_HEIGHT),
    );
    this.emulator?.setZoom(Math.min(Math.max(steps, 1), MAX_ZOOM));
  }
}
