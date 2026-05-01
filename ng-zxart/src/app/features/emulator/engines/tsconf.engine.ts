import {EmulatorEngine, EmulatorType} from './emulator-engine';
import {MameEmulatorInstance, MameGlobals, loadScriptOnce} from './mame-globals';

const LOADER_URL = '/libs/mame/loader.js';
const LIB_BASE = '/libs/mame';

export class TsconfEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'tsconf';

  private emulator: MameEmulatorInstance | null = null;
  private readonly scriptState = {injected: false};

  async start(canvas: HTMLCanvasElement, fileUrl: string): Promise<void> {
    await loadScriptOnce(this.scriptState, LOADER_URL);
    this.emulator = this.bootEmulator(canvas, fileUrl);
  }

  setFullscreen(): void {
    this.emulator?.requestFullScreen();
  }

  destroy(): void {
    this.emulator = null;
  }

  private bootEmulator(canvas: HTMLCanvasElement, fileUrl: string): MameEmulatorInstance {
    const globals = window as unknown as MameGlobals;
    if (!globals.MAMELoader || !globals.Emulator) {
      throw new Error('MAME globals (MAMELoader / Emulator) are not available');
    }
    const {MAMELoader, Emulator} = globals;
    const filename = new URL(fileUrl, window.location.origin).pathname.split('/').pop() ?? '';
    const extension = filename.split('.').pop()?.toLowerCase() ?? '';
    const peripheral = extension === 'img' ? 'hard2' : extension === 'spg' ? 'dump' : 'flop1';
    const nvramDirectory = extension === 'img' ? 'nvramsd' : 'nvram';

    const loader = new MAMELoader(
      MAMELoader.driver('tsconf'),
      MAMELoader.nativeResolution(760, 576),
      MAMELoader.emulatorJS(`${LIB_BASE}/mame.js`),
      MAMELoader.emulatorWASM(`${LIB_BASE}/mame.wasm`),
      MAMELoader.mountFile('nvram/tsconf/glukrs_nvram', MAMELoader.fetchFile('CMOS', `${LIB_BASE}/nvram/tsconf_trdos/glukrs_nvram`)),
      MAMELoader.mountFile('nvramsd/tsconf/glukrs_nvram', MAMELoader.fetchFile('CMOS', `${LIB_BASE}/nvram/tsconf_sd/glukrs_nvram`)),
      MAMELoader.mountFile('cfg/tsconf.cfg', MAMELoader.fetchFile('Cfg', `${LIB_BASE}/cfg/tsconf.cfg`)),
      MAMELoader.mountFile('tsconf.zip', MAMELoader.fetchFile('Bios', `${LIB_BASE}/roms/tsconf.zip`)),
      MAMELoader.mountFile('betadisk.zip', MAMELoader.fetchFile('Beta', `${LIB_BASE}/roms/betadisk.zip`)),
      MAMELoader.mountFile('kb_ms_natural.zip', MAMELoader.fetchFile('Keyboard', `${LIB_BASE}/roms/kb_ms_natural.zip`)),
      MAMELoader.mountFile('zxbus_neogs.zip', MAMELoader.fetchFile('GS', `${LIB_BASE}/roms/zxbus_neogs.zip`)),
      MAMELoader.peripheral('cfg_directory', 'cfg'),
      MAMELoader.peripheral('nvram_directory', nvramDirectory),
      MAMELoader.extraArgs(['-zxbus1', 'neogs', '-uimodekey', 'DEL']),
      MAMELoader.mountFile(filename, MAMELoader.fetchFile(filename, fileUrl)),
      MAMELoader.peripheral(peripheral, filename),
    );

    const emulator = new Emulator(canvas, null, loader);
    emulator.start({waitAfterDownloading: false});
    return emulator;
  }
}
