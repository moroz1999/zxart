import {EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameBoot, MameEngine} from './mame.engine';
import {
  ATM_MACHINE,
  MameMachine,
  PENTEVO_MACHINE,
  PROFI_MACHINE,
  SCORPION_MACHINE,
  SPRINTER_MACHINE,
} from './mame-machine';
import {extensionOf, fetchReleaseFiles} from './release-files';
import {ArchiveFile} from './zip-archive';
import {sclToTrd} from './scl-to-trd';
import {padTrdImage} from './trd-image';

/**
 * The machines a release is handed one file to: a TR-DOS disk they boot, or a
 * snapshot loaded straight into memory.
 *
 * They differ only in which MAME driver they are and what is plugged into
 * them — all of that is in the machine profile — so one engine runs them all.
 * What they share is the Beta Disk: a Scorpion, an ATM Turbo, a Profi, a ZX
 * Evolution and a Sprinter all boot TR-DOS off `flop1`.
 */
const MACHINES: Record<string, MameMachine> = {
  scorpion: SCORPION_MACHINE,
  atm: ATM_MACHINE,
  profi: PROFI_MACHINE,
  pentevo: PENTEVO_MACHINE,
  sprinter: SPRINTER_MACHINE,
};

/** The engine ids this engine answers for. */
export type BetaDiskEmulator = 'scorpion' | 'atm' | 'profi' | 'pentevo' | 'sprinter';

export class BetaDiskEngine extends MameEngine {
  readonly type: EmulatorType;
  protected readonly machine: MameMachine;

  constructor(type: BetaDiskEmulator) {
    super();
    this.type = type;
    this.machine = MACHINES[type];
  }

  protected async prepare(fileUrl: string, options: EmulatorStartOptions): Promise<MameBoot> {
    options.onStatus?.('emulator.status.downloading');
    const release = await fetchReleaseFiles(fileUrl, options.launchFilePath);
    const launchFile = this.chooseLaunchFile(release, options.launchFilePath);

    options.onStatus?.('emulator.status.starting', {name: launchFile.path});
    return this.openMedium(launchFile);
  }

  /**
   * The file the machine is started from. The backend says which it is; a
   * release that is one file is that file.
   */
  private chooseLaunchFile(release: ArchiveFile[], launchFilePath?: string): ArchiveFile {
    const named = launchFilePath ? release.find(file => file.path === launchFilePath) : undefined;
    const file = named ?? release[0];
    if (!file) {
      throw new Error('The release holds no file to start');
    }
    return file;
  }

  /**
   * How MAME has to be handed the launch file, by what it is.
   *
   * Whatever the release calls it, it is mounted under a name carrying its
   * format and nothing else: every device here picks the format from the
   * extension, and a published name may hold spaces, brackets or a second dot.
   */
  private openMedium(launchFile: ArchiveFile): MameBoot {
    const data = launchFile.data;
    switch (extensionOf(launchFile.path)) {
      case 'scl':
        // MAME's floppy cannot read an SCL, so it is laid out as the TR-DOS
        // disk it holds — see scl-to-trd.ts.
        return this.floppy(sclToTrd(data));
      case 'trd':
        // Plenty of published TRDs are trimmed to the sectors they use, and
        // MAME refuses those — see trd-image.ts.
        return this.floppy(padTrdImage(data));
      case 'img':
      case 'hdf':
        return {
          mounted: [{path: 'release.img', data}],
          args: ['-hard1', 'release.img'],
        };
      // Everything else reaching here is a snapshot the backend named as
      // runnable, which the snapshot device loads into memory and runs.
      default:
        return {
          mounted: [{path: `release.${extensionOf(launchFile.path)}`, data}],
          args: ['-dump', `release.${extensionOf(launchFile.path)}`],
        };
    }
  }

  private floppy(data: Uint8Array): MameBoot {
    return {
      mounted: [{path: 'release.trd', data}],
      args: ['-flop1', 'release.trd'],
    };
  }
}
