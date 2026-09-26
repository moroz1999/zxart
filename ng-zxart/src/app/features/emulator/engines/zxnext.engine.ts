import {EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameBoot, MameEngine} from './mame.engine';
import {MameMachine, ZXNEXT_MACHINE} from './mame-machine';
import {MAME_LIB_BASE} from './mame-loader';
import {buildFatCard, CardFile} from './fat-card';
import {extensionOf, fetchReleaseFiles} from './release-files';
import {ArchiveFile, readZipArchive} from './zip-archive';

/** The NextZXOS system tree the card is built around, downloaded once and cached. */
const SYSTEM_ZIP_URL = `${MAME_LIB_BASE}/next/nextzxos.zip`;

/** The card-root folder the release is staged into. */
const RELEASE_FOLDER = 'zxart';
/** NextZXOS runs this NextBASIC program at the end of its boot. */
const AUTOEXEC_PATH = 'nextzxos/autoexec.bas';
/** What the card is mounted as: the Next boots NextZXOS off it. */
const CARD_NAME = 'next.img';
/** The BASIC keywords the launch lines need, as the interpreter stores them. */
const TOKEN_SPECTRUM = 0xa3;
const TOKEN_LOAD = 0xef;

/**
 * What NextZXOS is told to run, by extension, following its own rules in
 * `c:/nextzxos/browser.cfg`. These are the formats whose rule there is a
 * single statement; the rest need several, with variables and line numbers.
 */
const AUTOSTART_COMMANDS: Record<string, (name: string) => number[]> = {
  nex: name => ascii(`.nexload ${name}`),
  // A dot command named by its path rather than by the name of one in `c:/dot`
  dot: name => ascii(`../${name}`),
  // SPECTRUM "name" — how NextZXOS starts a snapshot
  snx: name => [TOKEN_SPECTRUM, ...ascii(`"${name}"`)],
  // LOAD "name" — a NextBASIC program saved with LINE runs itself
  bas: name => [TOKEN_LOAD, ...ascii(`"${name}"`)],
};

export class ZxNextEngine extends MameEngine {
  readonly type: EmulatorType = 'zxnext';
  protected readonly machine: MameMachine = ZXNEXT_MACHINE;

  protected async prepare(fileUrl: string, options: EmulatorStartOptions): Promise<MameBoot> {
    options.onStatus?.('emulator.status.downloading');
    const [system, release] = await Promise.all([
      this.fetchSystemTree(),
      fetchReleaseFiles(fileUrl, options.launchFilePath),
    ]);

    const staged = this.stageRelease(release, options.launchFilePath);
    options.onStatus?.('emulator.status.staging');
    const card = buildFatCard([...system, ...staged.files, ...this.autoexecFor(staged.launchName)]);

    options.onStatus?.('emulator.status.starting', {name: staged.launchName ?? 'NextZXOS'});
    return {
      mounted: [{path: CARD_NAME, data: card}],
      args: ['-hard1', CARD_NAME],
    };
  }

  /** The stock NextZXOS tree, exactly as the pinned distribution holds it. */
  private async fetchSystemTree(): Promise<CardFile[]> {
    const response = await fetch(SYSTEM_ZIP_URL);
    if (!response.ok) {
      throw new Error(`NextZXOS system files are missing (HTTP ${response.status})`);
    }
    return readZipArchive(await response.arrayBuffer());
  }

  /**
   * Put the release under one card-root folder, with the launch file's own
   * directory flattened onto it. Titles build data paths from where they run,
   * or open their own files by name, so what sat beside the program in the
   * archive has to sit beside it on the card.
   */
  private stageRelease(
    release: ArchiveFile[],
    launchFilePath?: string,
  ): {files: CardFile[]; launchName: string | null} {
    const launchFile = launchFilePath
      ? release.find(file => file.path === launchFilePath) ?? null
      : null;
    const slash = launchFile ? launchFile.path.lastIndexOf('/') : -1;
    const launchDir = slash < 0 ? '' : launchFile!.path.slice(0, slash + 1);
    // A space would end the argument of the command that starts the release,
    // and a .nex does not know its own name, so the one file that is named
    // out loud is the one that gets a name that can be said.
    const launchName = launchFile ? launchFile.path.slice(slash + 1).replace(/\s+/g, '_') : null;

    const files = release.map(file => ({
      path:
        file === launchFile
          ? `${RELEASE_FOLDER}/${launchName}`
          : `${RELEASE_FOLDER}/${file.path.startsWith(launchDir) ? file.path.slice(launchDir.length) : file.path}`,
      data: file.data,
    }));

    return {files, launchName};
  }

  /**
   * NextZXOS runs `c:/nextzxos/autoexec.bas` once it is up, which is where the
   * release is started from — no keystrokes into a menu, no waiting on one.
   *
   * Only the formats in `AUTOSTART_COMMANDS` are started this way: their rule
   * is one plain-text statement, and a dot command is stored as the text it is
   * typed as. Every other format's rule is tokenised BASIC — a tape goes
   * through the TAP Loader, a snapshot through `snapload.bas` — so such a
   * release is left on the card for the Browser to start.
   */
  private autoexecFor(launchName: string | null): CardFile[] {
    const command = launchName ? AUTOSTART_COMMANDS[extensionOf(launchName)] : undefined;
    if (!launchName || !command) {
      return [];
    }
    // Stand in the release's folder before starting it, the way the NextZXOS
    // Browser does: a title opens its own data files against the current
    // directory, which at boot is the root of the card and not where the
    // release is.
    //
    // Neither line carries a drive letter: `:` ends a statement in NextBASIC,
    // so `c:/...` would reach the command as a bare `c`. A leading slash is
    // the root of the current drive, which at boot is the card.
    return [
      {
        path: AUTOEXEC_PATH,
        data: nextBasicProgram([ascii(`.cd /${RELEASE_FOLDER}`), command(launchName)]),
      },
    ];
  }

}

/** A line's plain characters, which is how a dot command and a file name are stored. */
function ascii(text: string): number[] {
  return [...text].map(character => character.charCodeAt(0) & 0xff);
}

/**
 * A NextBASIC program in the +3DOS file the OS loads it from: a 128-byte
 * header, then the lines. The lines hold no BASIC keywords — a dot command is
 * stored as the plain text it is typed as — so nothing here has to tokenise.
 */
function nextBasicProgram(lines: number[][]): Uint8Array {
  const FIRST_LINE = 10;
  const LINE_STEP = 10;
  const encoded: number[] = [];
  lines.forEach((line, index) => {
    const lineNumber = FIRST_LINE + index * LINE_STEP;
    const length = line.length + 1; // every BASIC line ends with ENTER
    encoded.push((lineNumber >> 8) & 0xff, lineNumber & 0xff); // the one big-endian field
    encoded.push(length & 0xff, (length >> 8) & 0xff);
    encoded.push(...line, 0x0d);
  });
  const program = Uint8Array.from(encoded);

  const file = new Uint8Array(128 + program.length);
  const view = new DataView(file.buffer);
  const signature = 'PLUS3DOS';
  for (let i = 0; i < signature.length; i++) {
    file[i] = signature.charCodeAt(i);
  }
  file[8] = 0x1a; // soft end of file
  file[9] = 0x01; // issue
  file[10] = 0x00; // version
  view.setUint32(11, file.length, true);
  file[15] = 0x00; // a BASIC program
  view.setUint16(16, program.length, true);
  view.setUint16(18, FIRST_LINE, true); // the line it auto-runs from
  view.setUint16(20, program.length, true); // no variables follow the program
  let checksum = 0;
  for (let i = 0; i < 127; i++) {
    checksum = (checksum + file[i]) & 0xff;
  }
  file[127] = checksum;
  file.set(program, 128);
  return file;
}
