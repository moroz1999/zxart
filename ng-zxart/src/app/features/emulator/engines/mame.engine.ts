import {EmulatorEngine, EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameFile, MameInstance, MAME_LIB_BASE, blobUrl, loadMameLoader} from './mame-loader';
import {MachineFile, MameMachine, MAME_COMMON_ARGS} from './mame-machine';

/** The hardware code for a second AY. */
const TURBO_SOUND = 'ts';
/** The hardware codes one NeoGS card answers for. */
const GENERAL_SOUND = ['gs', 'ngs'];

/** Bytes the page already holds, mounted under the name the machine opens. */
export interface MountedFile {
  path: string;
  data: Uint8Array;
}

/** What a machine is handed for one play, beyond its own fixed files. */
export interface MameBoot {
  /** Release data: a disk, a snapshot, a card the engine has just built. */
  mounted?: MountedFile[];
  /** Fixed files this play needs beyond the machine's own, by where they are served. */
  files?: MachineFile[];
  /** Switches naming those files, and anything else this play needs. */
  args?: string[];
  /** The NVRAM directory this play comes up in, when the machine has several. */
  nvramDirectory?: string;
}

/**
 * Everything a MAME machine does in the dialog that is not about the release
 * it is handed: starting the core, keeping the picture the right shape,
 * going quiet with the tab, full screen and reset.
 *
 * A machine that only mounts a file needs nothing beyond `machine` and the
 * boot it returns from {@link prepare}; one that builds an SD card does that
 * work there too.
 */
export abstract class MameEngine implements EmulatorEngine {
  abstract readonly type: EmulatorType;

  /** The machine this engine launches MAME as. */
  protected abstract readonly machine: MameMachine;

  private instance: MameInstance | null = null;
  private container: HTMLElement | null = null;
  private objectUrls: string[] = [];
  private readonly visibilityHandler = () => {
    if (document.hidden) {
      this.instance?.mute();
      window.Module?.pauseMainLoop?.();
    } else {
      window.Module?.resumeMainLoop?.();
      this.instance?.unmute();
    }
  };

  /**
   * What this play hands the machine. Called once the loader is there and
   * before the core starts, so it is where a release is downloaded, unpacked
   * and staged.
   */
  protected abstract prepare(fileUrl: string, options: EmulatorStartOptions): Promise<MameBoot>;

  async start(
    canvas: HTMLCanvasElement,
    fileUrl: string,
    container: HTMLElement,
    options: EmulatorStartOptions = {},
  ): Promise<void> {
    const loader = await loadMameLoader();
    const boot = await this.prepare(fileUrl, options);

    this.container = container;
    document.addEventListener('visibilitychange', this.visibilityHandler);

    const machine = this.machine;
    // MAME measures the canvas when SDL brings its window up and then draws
    // into whatever shape it found, so it is given the machine's size before
    // the core starts rather than nudged afterwards.
    canvas.width = machine.width;
    canvas.height = machine.height;

    this.instance = loader.start({
      canvas,
      driver: machine.driver,
      emulatorJS: `${MAME_LIB_BASE}/mame.js`,
      files: this.filesFor(boot),
      args: [
        ...MAME_COMMON_ARGS,
        '-resolution', `${machine.width}x${machine.height}`,
        // Without it the window is created at MAME's minimum bounds and the
        // picture arrives scaled down.
        '-maximize',
        ...machine.args,
        ...this.soundArgs(options.hardware ?? []),
        ...(boot.args ?? []),
      ],
      cfgDir: machine.cfgDirectory ?? null,
      nvramDir: boot.nvramDirectory ?? machine.nvramDirectory ?? null,
      diffDir: true,
      bgfxInitialChain: 'unfiltered',
      cache: `zxart-${machine.driver}`,
      onProgress: fraction => options.onStatus?.('emulator.status.downloading-core', {
        percent: Math.floor(fraction * 100),
      }),
    });

    await this.settleCanvas();
  }

  setFullscreen(): void {
    void this.container?.requestFullscreen?.();
  }

  restart(): void {
    this.instance?.softReset();
  }

  destroy(): void {
    document.removeEventListener('visibilitychange', this.visibilityHandler);
    window.Module?.pauseMainLoop?.();
    this.instance?.mute();
    this.instance = null;
    this.container = null;
    this.releaseObjectUrls();
  }

  /**
   * The sound cards this release asks for, as far as the machine can take
   * them. A release that names neither keeps the machine's own single AY, and
   * one whose machine carries a card as standard gets it either way.
   */
  private soundArgs(hardware: string[]): string[] {
    const machine = this.machine;
    const args: string[] = [];
    if (machine.turboSoundArgs && hardware.includes(TURBO_SOUND)) {
      args.push(...machine.turboSoundArgs);
    }
    if (machine.neoGsArgs && hardware.some(code => GENERAL_SOUND.includes(code))) {
      args.push(...machine.neoGsArgs);
    }
    return args;
  }

  /** Everything the machine finds in its filesystem, fixed files and release alike. */
  private filesFor(boot: MameBoot): MameFile[] {
    const machine = this.machine;
    const files: MameFile[] = [
      ...machine.roms.map(rom => ({url: `${MAME_LIB_BASE}/roms/${rom}`, path: `roms/${rom}`})),
      ...[...(machine.files ?? []), ...(boot.files ?? [])].map(file => ({
        url: `${MAME_LIB_BASE}/${file.source}`,
        path: file.target,
      })),
    ];
    for (const file of boot.mounted ?? []) {
      const url = blobUrl(file.data);
      this.objectUrls.push(url);
      files.push({url, path: file.path});
    }
    return files;
  }

  /**
   * Waits for the machine to come up, then settles the canvas on the picture
   * it actually draws.
   *
   * The profile's size is a guess made from the machine's reference page; the
   * core knows its own visible area and only once it is running. Doubling that
   * area is what keeps pixels whole — a size that is not a multiple of it makes
   * the picture crawl.
   *
   * The wait is what the dialog shows its progress against, so it has to cover
   * the download of a 34 MB core on a slow line as well as the boot after it.
   * It gives up rather than waiting forever: a machine that never draws leaves
   * the dialog open with whatever MAME put on the canvas.
   */
  private settleCanvas(): Promise<void> {
    const SETTLE_INTERVAL_MS = 250;
    const SETTLE_BUDGET_MS = 5 * 60 * 1000;
    const deadline = Date.now() + SETTLE_BUDGET_MS;

    return new Promise<void>(resolve => {
      const settle = () => {
        if (!this.instance) {
          resolve(); // torn down while waiting
          return;
        }
        const area = this.instance.visibleArea();
        if (!area) {
          Date.now() < deadline ? setTimeout(settle, SETTLE_INTERVAL_MS) : resolve();
          return;
        }
        this.instance.resizeBacking(area.width * 2, area.height * 2);
        this.releaseObjectUrls();
        resolve();
      };

      setTimeout(settle, SETTLE_INTERVAL_MS);
    });
  }

  /** The machine has read what it was handed, so the names can go. */
  private releaseObjectUrls(): void {
    for (const url of this.objectUrls) {
      URL.revokeObjectURL(url);
    }
    this.objectUrls = [];
  }
}
