import {EmulatorEngine, EmulatorType} from './emulator-engine';
import {MameEmulatorInstance, MameGlobals} from './mame-globals';
import {loadScriptOnce} from './load-script';
import {settleCanvasSize} from './mame-canvas';
import {useInMemoryFileSystem} from './mame-memory-fs';

const BROWSERFS_URL = '/libs/mamenextsam/browserfs.min.js';
const LOADER_URL = '/libs/mamenextsam/loader.js';
const LIB_BASE = '/libs/mamenextsam';

/**
 * The size the SAM runs at in the dialog, and the machine's own: it is what
 * MAME is launched with and what the canvas is nudged back to, so no other
 * emulator's resolution reaches this one.
 */
const NATIVE_WIDTH = 576;
const NATIVE_HEIGHT = 550;

export class SamcoupeEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'samcoupe';

  private emulator: MameEmulatorInstance | null = null;
  private canvas: HTMLCanvasElement | null = null;
  private readonly pointerLockHandler = () => {
    void this.canvas?.requestPointerLock();
  };
  private readonly visibilityHandler = () => {
    if (document.hidden) {
      this.emulator?.mute();
      window.Module?.pauseMainLoop?.();
    } else {
      window.Module?.resumeMainLoop?.();
      this.emulator?.unmute();
    }
  };
  private readonly browserFsState = {injected: false};
  private readonly scriptState = {injected: false};

  async start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void> {
    await loadScriptOnce(this.browserFsState, BROWSERFS_URL);
    await loadScriptOnce(this.scriptState, LOADER_URL);
    await useInMemoryFileSystem();
    this.canvas = canvas;
    canvas.addEventListener('click', this.pointerLockHandler);
    document.addEventListener('visibilitychange', this.visibilityHandler);
    this.emulator = this.bootEmulator(canvas, fileUrl);
  }

  setFullscreen(): void {
    this.emulator?.requestFullScreen();
  }

  destroy(): void {
    document.removeEventListener('visibilitychange', this.visibilityHandler);
    this.canvas?.removeEventListener('click', this.pointerLockHandler);
    this.canvas = null;
    window.Module?.pauseMainLoop?.();
    this.emulator?.mute();
    this.emulator = null;
  }

  private bootEmulator(canvas: HTMLCanvasElement, fileUrl: string): MameEmulatorInstance {
    const globals = window as unknown as MameGlobals;
    if (!globals.MAMELoader || !globals.Emulator) {
      throw new Error('MAME globals (MAMELoader / Emulator) are not available');
    }
    const {MAMELoader, Emulator} = globals;
    const filename = new URL(fileUrl, window.location.origin).pathname.split('/').pop() ?? '';

    const loader = new MAMELoader(
      MAMELoader.driver('samcoupe'),
      MAMELoader.nativeResolution(NATIVE_WIDTH, NATIVE_HEIGHT),
      MAMELoader.emulatorJS(`${LIB_BASE}/mame.js`),
      MAMELoader.emulatorWASM(`${LIB_BASE}/mame.wasm`),
      MAMELoader.mountFile('samcoupe.zip', MAMELoader.fetchFile('Bios', `${LIB_BASE}/roms/samcoupe.zip`)),
      MAMELoader.mountFile(filename, MAMELoader.fetchFile('Disk', fileUrl)),
      MAMELoader.peripheral('flop1', filename),
      MAMELoader.extraArgs([
        '-mouseport', 'mouse',
        '-uimodekey', 'DEL',
        '-ab', '........................boot\\n',
      ]),
    );

    const emulator = new Emulator(canvas, null, loader);
    emulator.start({waitAfterDownloading: false});
    settleCanvasSize(NATIVE_WIDTH, NATIVE_HEIGHT, () => this.emulator !== null);
    return emulator;
  }
}
