import {EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameBoot, MameEngine} from './mame.engine';
import {MameMachine, TSCONF_MACHINE} from './mame-machine';
import {buildFatCard} from './fat-card';
import {extensionOf, fetchReleaseFiles} from './release-files';
import {ArchiveFile} from './zip-archive';
import {sclToTrd} from './scl-to-trd';
import {padTrdImage} from './trd-image';

/**
 * TSConf is handed the release **whole** rather than one file picked out of
 * it, because a TSConf program opens its data files off the SD card while it
 * runs.
 *
 * Which device the launch file is mounted on also decides the TS-BIOS
 * configuration the machine comes up in, and those are two saved NVRAM
 * directories differing in one setting: what the machine boots from.
 */
const CARD_NAME = 'card.img';

/** The saved TS-BIOS configuration the machine boots from, per medium. */
const NVRAM_SD = 'nvramsd';
const NVRAM_TRDOS = 'nvramtrd';

export class TsconfEngine extends MameEngine {
  readonly type: EmulatorType = 'tsconf';
  protected readonly machine: MameMachine = TSCONF_MACHINE;

  protected async prepare(fileUrl: string, options: EmulatorStartOptions): Promise<MameBoot> {
    options.onStatus?.('emulator.status.downloading');
    const release = await fetchReleaseFiles(fileUrl, options.launchFilePath);
    const launchFile = this.chooseLaunchFile(release, options.launchFilePath);
    const format = extensionOf(launchFile.path);

    if (format === 'img') {
      // The release ships a whole SD card; the machine boots off it, which is
      // what the saved TS-BIOS configuration for the SD is for.
      return this.boot(NVRAM_SD, 'sd', [
        {path: CARD_NAME, data: launchFile.data},
      ], ['-hard1', CARD_NAME]);
    }

    if (format === 'trd' || format === 'scl') {
      // MAME's floppy cannot read an SCL, so it is laid out as the TR-DOS disk
      // it holds — see scl-to-trd.ts.
      const disk = format === 'scl' ? sclToTrd(launchFile.data) : padTrdImage(launchFile.data);
      return this.boot(NVRAM_TRDOS, 'trd', [
        {path: 'release.trd', data: disk},
      ], ['-flop1', 'release.trd']);
    }

    // An SPG is loaded into memory whole and then run, and a title that does
    // not fit in memory opens the rest of itself by name — Another World reads
    // `/ANOTHER/bank01`. So it also gets a card, holding every file of the
    // release at the path it was published under, which is where the release's
    // own instructions say to copy it.
    options.onStatus?.('emulator.status.staging');
    const image = release.find(file => extensionOf(file.path) === 'img');
    return this.boot(NVRAM_TRDOS, 'trd', [
      {path: 'release.spg', data: launchFile.data},
      {path: CARD_NAME, data: image ? image.data : buildFatCard(release)},
    ], ['-dump', 'release.spg', '-hard1', CARD_NAME]);
  }

  /**
   * One play, with the TS-BIOS configuration it boots in. MAME reads NVRAM
   * from one directory, so the saved configuration is mounted under the name
   * the machine writes — `glukrs`, the Mr Gluk Reset Service's own store.
   */
  private boot(
    nvramDirectory: string,
    saved: string,
    mounted: {path: string; data: Uint8Array}[],
    args: string[],
  ): MameBoot {
    return {
      nvramDirectory,
      mounted,
      args,
      files: [{
        source: `nvram/tsconf2/glukrs_${saved}`,
        target: `${nvramDirectory}/${this.machine.driver}/glukrs`,
      }],
    };
  }

  private chooseLaunchFile(release: ArchiveFile[], launchFilePath?: string): ArchiveFile {
    const named = launchFilePath ? release.find(file => file.path === launchFilePath) : undefined;
    const file = named ?? release[0];
    if (!file) {
      throw new Error('The release holds no file to start');
    }
    return file;
  }
}
