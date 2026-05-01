import {EmulatorEngine, EmulatorType} from './emulator-engine';
import {MameEmulatorInstance, MameGlobals, loadScriptOnce} from './mame-globals';

const LOADER_URL = '/libs/mamenextsam/loader.js';
const LIB_BASE = '/libs/mamenextsam';

export class SamcoupeEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'samcoupe';

  private emulator: MameEmulatorInstance | null = null;
  private canvas: HTMLCanvasElement | null = null;
  private readonly pointerLockHandler = () => {
    void this.canvas?.requestPointerLock();
  };
  private readonly scriptState = {injected: false};

  async start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void> {
    await loadScriptOnce(this.scriptState, LOADER_URL);
    this.canvas = canvas;
    canvas.addEventListener('click', this.pointerLockHandler);
    this.emulator = this.bootEmulator(canvas, fileUrl);
  }

  setFullscreen(): void {
    this.emulator?.requestFullScreen();
  }

  destroy(): void {
    this.canvas?.removeEventListener('click', this.pointerLockHandler);
    this.canvas = null;
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
      MAMELoader.nativeResolution(576, 550),
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
    return emulator;
  }
}
