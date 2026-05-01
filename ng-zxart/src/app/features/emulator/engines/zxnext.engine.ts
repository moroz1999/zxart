import {EmulatorEngine, EmulatorType} from './emulator-engine';
import {MameEmulatorInstance, MameGlobals, loadScriptOnce} from './mame-globals';

const LOADER_URL = '/libs/mamenextsam/loader.js';
const LIB_BASE = '/libs/mamenextsam';
const SYSTEM_IMAGE_URL = `${LIB_BASE}/software/cspect-next-1gb.img`;

export class ZxNextEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'zxnext';

  private emulator: MameEmulatorInstance | null = null;
  private canvas: HTMLCanvasElement | null = null;
  private readonly pointerLockHandler = () => {
    void this.canvas?.requestPointerLock();
  };
  private readonly scriptState = {injected: false};

  async start(canvas: HTMLCanvasElement, _fileUrl: string): Promise<void> {
    await loadScriptOnce(this.scriptState, LOADER_URL);
    this.canvas = canvas;
    canvas.addEventListener('click', this.pointerLockHandler);
    this.emulator = this.bootEmulator(canvas);
  }

  setFullscreen(): void {
    this.emulator?.requestFullScreen();
  }

  destroy(): void {
    this.canvas?.removeEventListener('click', this.pointerLockHandler);
    this.canvas = null;
    this.emulator = null;
  }

  private bootEmulator(canvas: HTMLCanvasElement): MameEmulatorInstance {
    const globals = window as unknown as MameGlobals;
    if (!globals.MAMELoader || !globals.Emulator) {
      throw new Error('MAME globals (MAMELoader / Emulator) are not available');
    }
    const {MAMELoader, Emulator} = globals;

    const loader = new MAMELoader(
      MAMELoader.driver('tbblue'),
      MAMELoader.nativeResolution(672, 544),
      MAMELoader.emulatorJS(`${LIB_BASE}/mame.js`),
      MAMELoader.emulatorWASM(`${LIB_BASE}/mame.wasm`),
      MAMELoader.mountFile('tbblue.zip', MAMELoader.fetchFile('Bios', `${LIB_BASE}/roms/tbblue.zip`)),
      MAMELoader.mountFile('next.img', MAMELoader.fetchFile('Disk', SYSTEM_IMAGE_URL)),
      MAMELoader.peripheral('hard1', 'next.img'),
      MAMELoader.extraArgs(['-uimodekey', 'DEL']),
    );

    const emulator = new Emulator(canvas, null, loader);
    emulator.start({waitAfterDownloading: false});
    return emulator;
  }
}
