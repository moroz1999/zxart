import {EmulatorEngine, EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameEmulatorInstance, MameGlobals} from './mame-globals';
import {loadScriptOnce} from './load-script';
import {buildFatCard} from './fat-card';
import {settleCanvasSize} from './mame-canvas';
import {useInMemoryFileSystem} from './mame-memory-fs';
import {extensionOf, fetchReleaseFiles} from './release-files';
import {ArchiveFile} from './zip-archive';
import {sclToTrd} from './scl-to-trd';

const BROWSERFS_URL = '/libs/mame/browserfs.min.js';
const LOADER_URL = '/libs/mame/loader.js';
const LIB_BASE = '/libs/mame';

/**
 * The size TSConf runs at in the dialog, and the machine's own: it is what
 * MAME is launched with and what the canvas is nudged back to, so no other
 * emulator's resolution reaches this one.
 */
const NATIVE_WIDTH = 760;
const NATIVE_HEIGHT = 576;

const CARD_NAME = 'card.img';

/**
 * What MAME is told to do with the file the release is started from.
 *
 * `dump` is the snapshot device, which loads an SPG straight into memory and
 * runs it; `flop1` is the Beta Disk drive, which TR-DOS boots from; `hard2` is
 * the TSConf's own SD card — the NeoGS in `zxbus1` holds `hard1`.
 */
interface Media {
  peripheral: 'dump' | 'flop1' | 'hard2';
  /**
   * What the medium is mounted as. Every device here picks its format from
   * the extension, so the name carries it and nothing else: the release's own
   * name may hold spaces, brackets or a second dot.
   */
  name: string;
  /** Which of the two saved TS-BIOS configurations the machine comes up in. */
  nvramDirectory: 'nvram' | 'nvramsd';
  data: Uint8Array;
}

export class TsconfEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'tsconf';

  private emulator: MameEmulatorInstance | null = null;
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

  async start(
    canvas: HTMLCanvasElement,
    fileUrl: string,
    _container: HTMLElement,
    options: EmulatorStartOptions = {},
  ): Promise<void> {
    await loadScriptOnce(this.browserFsState, BROWSERFS_URL);
    await loadScriptOnce(this.scriptState, LOADER_URL);

    options.onStatus?.('emulator.status.downloading');
    const release = await fetchReleaseFiles(fileUrl, options.launchFilePath);
    const launchFile = this.chooseLaunchFile(release, options.launchFilePath);
    const media = this.openMedium(launchFile);

    if (media.peripheral === 'dump') {
      options.onStatus?.('emulator.status.staging');
    }
    const card = this.cardFor(release, media);

    options.onStatus?.('emulator.status.starting', {name: launchFile.path});
    await useInMemoryFileSystem();
    document.addEventListener('visibilitychange', this.visibilityHandler);
    this.emulator = this.bootEmulator(canvas, media, card);
  }

  setFullscreen(): void {
    this.emulator?.requestFullScreen();
  }

  destroy(): void {
    document.removeEventListener('visibilitychange', this.visibilityHandler);
    window.Module?.pauseMainLoop?.();
    this.emulator?.mute();
    this.emulator = null;
  }

  /**
   * The file the machine is started from. The backend says which it is; a
   * release that is one file is that file, and anything else is a release
   * whose parsed structure the emulator was offered for in the first place.
   */
  private chooseLaunchFile(release: ArchiveFile[], launchFilePath?: string): ArchiveFile {
    const named = launchFilePath
      ? release.find(file => file.path === launchFilePath)
      : undefined;
    const file = named ?? release[0];
    if (!file) {
      throw new Error('The release holds no file to start');
    }
    return file;
  }

  /** How MAME has to be handed the launch file, by what it is. */
  private openMedium(launchFile: ArchiveFile): Media {
    switch (extensionOf(launchFile.path)) {
      case 'img':
        // The release ships a whole SD card; the machine boots off it, which
        // is what the saved TS-BIOS configuration for the SD is for.
        return {
          peripheral: 'hard2',
          name: 'release.img',
          nvramDirectory: 'nvramsd',
          data: launchFile.data,
        };
      case 'trd':
        return {
          peripheral: 'flop1',
          name: 'release.trd',
          nvramDirectory: 'nvram',
          data: launchFile.data,
        };
      case 'scl':
        // MAME's floppy cannot read an SCL, so it is laid out as the TR-DOS
        // disk it holds — see scl-to-trd.ts.
        return {
          peripheral: 'flop1',
          name: 'release.trd',
          nvramDirectory: 'nvram',
          data: sclToTrd(launchFile.data),
        };
      // `spg`, and nothing else reaches here: the backend names one of the
      // four runnable types, and a release holding none of them is not
      // offered for playing at all.
      default:
        return {
          peripheral: 'dump',
          name: 'release.spg',
          nvramDirectory: 'nvram',
          data: launchFile.data,
        };
    }
  }

  /**
   * The SD card the machine finds in its slot, or null when it needs none.
   *
   * A TSConf program is loaded into memory whole and then opens its own data
   * files on the card by the paths they were published under — Another World
   * reads `/ANOTHER/bank01` — so the release is written onto a card exactly as
   * it is packed. A release that ships an SD card image of its own is that
   * card. A TR-DOS release needs no card: its disk is the medium.
   */
  private cardFor(release: ArchiveFile[], media: Media): Uint8Array | null {
    if (media.peripheral !== 'dump') {
      return null;
    }
    const image = release.find(file => extensionOf(file.path) === 'img');
    return image ? image.data : buildFatCard(release);
  }

  private bootEmulator(
    canvas: HTMLCanvasElement,
    media: Media,
    card: Uint8Array | null,
  ): MameEmulatorInstance {
    const globals = window as unknown as MameGlobals;
    if (!globals.MAMELoader || !globals.Emulator) {
      throw new Error('MAME globals (MAMELoader / Emulator) are not available');
    }
    const {MAMELoader, Emulator} = globals;

    const cardArgs = card
      ? [
          MAMELoader.mountFile(CARD_NAME, MAMELoader.localFile('SD card', card)),
          MAMELoader.peripheral('hard2', CARD_NAME),
        ]
      : [];

    const loader = new MAMELoader(
      MAMELoader.driver('tsconf'),
      MAMELoader.nativeResolution(NATIVE_WIDTH, NATIVE_HEIGHT),
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
      MAMELoader.peripheral('nvram_directory', media.nvramDirectory),
      MAMELoader.extraArgs(['-zxbus1', 'neogs', '-uimodekey', 'DEL']),
      MAMELoader.mountFile(media.name, MAMELoader.localFile('Release', media.data)),
      MAMELoader.peripheral(media.peripheral, media.name),
      ...cardArgs,
    );

    const emulator = new Emulator(canvas, null, loader);
    emulator.start({waitAfterDownloading: false});
    settleCanvasSize(NATIVE_WIDTH, NATIVE_HEIGHT, () => this.emulator !== null);
    return emulator;
  }
}
