/**
 * Builds the SD card a machine is handed, in the browser.
 *
 * MAME mounts a raw card image and never writes files into it for us, so the
 * card is assembled here: a fresh FAT32 volume holding whatever the machine
 * has to find on it — the NextZXOS system tree and the release for the Next,
 * the release alone for TSConf. It is rebuilt per play, which leaves nothing
 * to generate or store on the server.
 */

import {EmulatorError} from './emulator-error';

/** One file as it lands on the card. `path` is slash-separated, from the root. */
export interface CardFile {
  path: string;
  data: Uint8Array;
}

const SECTOR_SIZE = 512;
/**
 * The card is always a power of two bytes, and never smaller than 64 MB.
 *
 * Two things constrain the size. MAME derives the card's geometry from the
 * image size, and only a size that factorises exactly covers every sector —
 * a power of two always does. Its SD device also needs the block count to be
 * a multiple of 1024 to present the image as SDHC, which is true from 512 KB
 * up. 64 MB is then the floor, because below it a FAT32 volume of 512-byte
 * clusters no longer reaches the 65525 clusters it must have.
 */
const MIN_TOTAL_BYTES = 64 * 1024 * 1024;
/**
 * The ceiling is the browser's memory rather than anything on the card: the
 * image is built whole and handed to the emulator whole. No Next release comes
 * close, and one that did would be better off not being played in a tab.
 */
const MAX_TOTAL_BYTES = 512 * 1024 * 1024;
/** The usual 1 MB alignment: the partition starts where every card's does. */
const PARTITION_LBA = 2048;
const PARTITION_TYPE_FAT32_LBA = 0x0c;

const RESERVED_SECTORS = 32;
const FAT_COUNT = 2;
/**
 * One sector per cluster. Nothing larger reaches the 65525 clusters a FAT32
 * volume must have at this size.
 */
const SECTORS_PER_CLUSTER = 1;
const ROOT_CLUSTER = 2;
const FSINFO_SECTOR = 1;
const BACKUP_BOOT_SECTOR = 6;

const FAT_EOC = 0x0fffffff;
const DIR_ENTRY_SIZE = 32;
const ATTR_DIRECTORY = 0x10;
const ATTR_ARCHIVE = 0x20;
const ATTR_LFN = 0x0f;
/** The Windows NT case flags, which spare an all-lower-case 8.3 name its long entry. */
const NT_LOWER_BASE = 0x08;
const NT_LOWER_EXT = 0x10;
/** A long name carries 13 UTF-16 code units per entry. */
const LFN_CHARS_PER_ENTRY = 13;
const LFN_LAST_MASK = 0x40;

/** A directory being assembled, before it is laid down on the card. */
interface DirNode {
  name: string;
  dirs: Map<string, DirNode>;
  files: {name: string; data: Uint8Array}[];
}

/**
 * The finished card image, ready to be handed to MAME as the SD card.
 *
 * Files are written in the order given; a path whose directories do not exist
 * yet creates them. Two files with the same path is a programming error and
 * the later one wins.
 */
export function buildFatCard(files: CardFile[]): Uint8Array {
  const root: DirNode = {name: '', dirs: new Map(), files: []};
  for (const file of files) {
    const parts = file.path.split('/').filter(part => part.length > 0);
    if (parts.length === 0) {
      continue;
    }
    const name = parts.pop()!;
    let dir = root;
    for (const part of parts) {
      let next = dir.dirs.get(part.toUpperCase());
      if (!next) {
        next = {name: part, dirs: new Map(), files: []};
        dir.dirs.set(part.toUpperCase(), next);
      }
      dir = next;
    }
    dir.files = dir.files.filter(existing => existing.name.toUpperCase() !== name.toUpperCase());
    dir.files.push({name, data: file.data});
  }

  return new CardWriter(totalSectorsFor(files)).write(root);
}

/**
 * The smallest card the release fits on, as a power of two bytes. Directories
 * and the slack of a part-used cluster are covered by rounding every file up
 * and adding a tenth; the card only has to be big enough, and the next size up
 * is a doubling anyway.
 */
function totalSectorsFor(files: CardFile[]): number {
  const clusterBytes = SECTORS_PER_CLUSTER * SECTOR_SIZE;
  let content = 0;
  for (const file of files) {
    content += Math.ceil(Math.max(file.data.length, 1) / clusterBytes) * clusterBytes;
  }
  const needed = content * 1.1;

  for (let total = MIN_TOTAL_BYTES; total <= MAX_TOTAL_BYTES; total *= 2) {
    if (needed <= total) {
      return total / SECTOR_SIZE;
    }
  }
  throw new EmulatorError('emulator.error.too-big', {
    size: Math.round(content / 1024 / 1024),
    limit: Math.round(MAX_TOTAL_BYTES / 1024 / 1024),
  });
}

/** Lays a directory tree down on a freshly formatted FAT32 volume. */
class CardWriter {
  private readonly partitionSectors: number;
  private readonly image: Uint8Array;
  private readonly view: DataView;
  private readonly fatSectors: number;
  private readonly dataStartSector: number;
  private readonly clusterCount: number;
  private nextFreeCluster = ROOT_CLUSTER;
  private usedClusters = 0;
  /** The FAT is built here and copied into both of its slots at the end. */
  private readonly fat: Uint32Array;

  constructor(totalSectors: number) {
    this.partitionSectors = totalSectors - PARTITION_LBA;
    this.image = new Uint8Array(totalSectors * SECTOR_SIZE);
    this.view = new DataView(this.image.buffer);

    // The FAT has to hold an entry for every cluster it leaves room for, so
    // its size is the fixed point of that dependency.
    let fatSectors = 1;
    for (;;) {
      const dataSectors = this.partitionSectors - RESERVED_SECTORS - FAT_COUNT * fatSectors;
      const clusters = Math.floor(dataSectors / SECTORS_PER_CLUSTER);
      const needed = Math.ceil(((clusters + 2) * 4) / SECTOR_SIZE);
      if (needed <= fatSectors) {
        break;
      }
      fatSectors = needed;
    }
    this.fatSectors = fatSectors;
    this.dataStartSector = PARTITION_LBA + RESERVED_SECTORS + FAT_COUNT * fatSectors;
    this.clusterCount = Math.floor(
      (this.partitionSectors - RESERVED_SECTORS - FAT_COUNT * fatSectors) / SECTORS_PER_CLUSTER,
    );
    this.fat = new Uint32Array(this.clusterCount + 2);
    this.fat[0] = 0x0ffffff8;
    this.fat[1] = 0x0fffffff;
  }

  write(root: DirNode): Uint8Array {
    this.writeMbr();
    this.writeBootSector();
    this.writeDirectory(root, this.allocate(this.clustersForDirectory(root)), 0);
    this.writeFat();
    this.writeFsInfo();
    return this.image;
  }

  // --- the volume itself ----------------------------------------------------

  private writeMbr(): void {
    const base = 446;
    this.image[base + 0] = 0x00; // not bootable: the machine boots from its own ROM
    this.image[base + 4] = PARTITION_TYPE_FAT32_LBA;
    // The CHS fields are what MAME derives from the image size. Nothing reads
    // them — the card is addressed by LBA — they are filled in for tidiness.
    this.image.set([0x00, 0x02, 0x00], base + 1);
    this.image.set([0xff, 0xff, 0xff], base + 5);
    this.view.setUint32(base + 8, PARTITION_LBA, true);
    this.view.setUint32(base + 12, this.partitionSectors, true);
    this.view.setUint16(510, 0xaa55, true);
  }

  private writeBootSector(): void {
    const boot = new Uint8Array(SECTOR_SIZE);
    const view = new DataView(boot.buffer);
    boot.set([0xeb, 0x58, 0x90], 0); // the jump every FAT driver looks for
    boot.set(asciiBytes('ZXART   '), 3);
    view.setUint16(11, SECTOR_SIZE, true);
    boot[13] = SECTORS_PER_CLUSTER;
    view.setUint16(14, RESERVED_SECTORS, true);
    boot[16] = FAT_COUNT;
    view.setUint16(17, 0, true); // FAT32 keeps its root directory in the data area
    view.setUint16(19, 0, true); // the 16-bit sector count is unused past 65535
    boot[21] = 0xf8; // fixed disk
    view.setUint16(22, 0, true); // FAT32 keeps the FAT size in the 32-bit field
    view.setUint16(24, 32, true); // sectors per track
    view.setUint16(26, 16, true); // heads
    view.setUint32(28, PARTITION_LBA, true);
    view.setUint32(32, this.partitionSectors, true);
    view.setUint32(36, this.fatSectors, true);
    view.setUint16(40, 0, true); // both FATs are live
    view.setUint16(42, 0, true); // version 0.0
    view.setUint32(44, ROOT_CLUSTER, true);
    view.setUint16(48, FSINFO_SECTOR, true);
    view.setUint16(50, BACKUP_BOOT_SECTOR, true);
    boot[64] = 0x80; // BIOS drive number
    boot[66] = 0x29; // extended boot signature
    view.setUint32(67, 0x5a584152, true); // volume serial
    boot.set(asciiBytes('ZXART      '), 71);
    boot.set(asciiBytes('FAT32   '), 82);
    view.setUint16(510, 0xaa55, true);

    this.image.set(boot, PARTITION_LBA * SECTOR_SIZE);
    this.image.set(boot, (PARTITION_LBA + BACKUP_BOOT_SECTOR) * SECTOR_SIZE);
  }

  private writeFsInfo(): void {
    for (const sector of [FSINFO_SECTOR, BACKUP_BOOT_SECTOR + FSINFO_SECTOR]) {
      const offset = (PARTITION_LBA + sector) * SECTOR_SIZE;
      this.view.setUint32(offset, 0x41615252, true);
      this.view.setUint32(offset + 484, 0x61417272, true);
      this.view.setUint32(offset + 488, this.clusterCount - this.usedClusters, true);
      this.view.setUint32(offset + 492, this.nextFreeCluster, true);
      this.view.setUint32(offset + 508, 0xaa550000, true);
    }
  }

  private writeFat(): void {
    const bytes = new Uint8Array(this.fat.length * 4);
    const view = new DataView(bytes.buffer);
    for (let i = 0; i < this.fat.length; i++) {
      view.setUint32(i * 4, this.fat[i], true);
    }
    for (let copy = 0; copy < FAT_COUNT; copy++) {
      const start = (PARTITION_LBA + RESERVED_SECTORS + copy * this.fatSectors) * SECTOR_SIZE;
      this.image.set(bytes, start);
    }
  }

  // --- clusters -------------------------------------------------------------

  /** Allocates a chain of `count` clusters and returns its first cluster. */
  private allocate(count: number): number {
    if (this.nextFreeCluster + count > this.clusterCount + 2) {
      // The card was sized for this content, so getting here is a bug in the
      // sizing rather than a release that is too big.
      throw new Error('The SD card ran out of clusters');
    }
    const first = this.nextFreeCluster;
    for (let i = 0; i < count; i++) {
      const cluster = first + i;
      this.fat[cluster] = i === count - 1 ? FAT_EOC : cluster + 1;
    }
    this.nextFreeCluster += count;
    this.usedClusters += count;
    return first;
  }

  private clusterOffset(cluster: number): number {
    return (this.dataStartSector + (cluster - ROOT_CLUSTER) * SECTORS_PER_CLUSTER) * SECTOR_SIZE;
  }

  private get clusterBytes(): number {
    return SECTORS_PER_CLUSTER * SECTOR_SIZE;
  }

  // --- the tree -------------------------------------------------------------

  private clustersForDirectory(dir: DirNode, isRoot = true): number {
    let entries = isRoot ? 0 : 2; // "." and ".."
    for (const child of dir.dirs.values()) {
      entries += directoryEntryCount(child.name);
    }
    for (const file of dir.files) {
      entries += directoryEntryCount(file.name);
    }
    return Math.max(1, Math.ceil((entries * DIR_ENTRY_SIZE) / this.clusterBytes));
  }

  /**
   * Writes one directory's entries into the clusters already allocated for it.
   * Children are laid down first, because an entry cannot be written before
   * the cluster it points at is known.
   */
  private writeDirectory(dir: DirNode, cluster: number, parentCluster: number): void {
    const entries: Uint8Array[] = [];
    // Every 8.3 name is claimed before anything is invented, so a generated
    // name can never take the one a file has a right to.
    const taken = new Set<string>();
    for (const name of [...[...dir.dirs.values()].map(child => child.name), ...dir.files.map(file => file.name)]) {
      const fitted = shortNameOf(name);
      if (fitted) {
        taken.add(fitted.short);
      }
    }

    if (parentCluster !== 0 || dir.name !== '') {
      entries.push(dotEntry('.', cluster));
      entries.push(dotEntry('..', parentCluster === ROOT_CLUSTER ? 0 : parentCluster));
    }

    for (const child of dir.dirs.values()) {
      const childCluster = this.allocate(this.clustersForDirectory(child, false));
      this.writeDirectory(child, childCluster, cluster);
      entries.push(...buildEntries(child.name, taken, childCluster, 0, true));
    }

    for (const file of dir.files) {
      const clusters = Math.max(1, Math.ceil(file.data.length / this.clusterBytes));
      const firstCluster = file.data.length === 0 ? 0 : this.allocate(clusters);
      if (firstCluster !== 0) {
        this.image.set(file.data, this.clusterOffset(firstCluster));
      }
      entries.push(...buildEntries(file.name, taken, firstCluster, file.data.length, false));
    }

    let offset = this.clusterOffset(cluster);
    for (const entry of entries) {
      this.image.set(entry, offset);
      offset += DIR_ENTRY_SIZE;
    }
  }
}

// --- directory entries ------------------------------------------------------

function directoryEntryCount(name: string): number {
  const fitted = shortNameOf(name);
  return 1 + (fitted && !fitted.needsLong ? 0 : Math.ceil(name.length / LFN_CHARS_PER_ENTRY));
}

/**
 * How a name fits 8.3, or null when it does not fit at all.
 *
 * The **8.3 name is what has to be right**: NextZXOS and the Next's boot ROM
 * look files up by it, so a name that fits must be filed under exactly its own
 * upper-cased form. A name in one case throughout carries the case flags and
 * needs nothing more; a mixed-case name keeps a long entry beside the same
 * short name, which is how the stock card carries `enNextZX.rom`.
 */
function shortNameOf(name: string): {short: string; ntFlags: number; needsLong: boolean} | null {
  const dot = name.lastIndexOf('.');
  const base = dot <= 0 ? name : name.slice(0, dot);
  const ext = dot <= 0 ? '' : name.slice(dot + 1);
  if (base.length === 0 || base.length > 8 || ext.length > 3 || ext.includes('.')) {
    return null;
  }
  const legal = /^[A-Za-z0-9$%'\-_@~`!(){}^#&]*$/;
  if (!legal.test(base) || !legal.test(ext)) {
    return null;
  }
  const short = base.toUpperCase().padEnd(8, ' ') + ext.toUpperCase().padEnd(3, ' ');
  const baseCase = caseOf(base);
  const extCase = caseOf(ext);
  if (baseCase === null || extCase === null) {
    return {short, ntFlags: 0, needsLong: true};
  }
  return {
    short,
    ntFlags: (baseCase === 'lower' ? NT_LOWER_BASE : 0) | (extCase === 'lower' ? NT_LOWER_EXT : 0),
    needsLong: false,
  };
}

/** 'upper', 'lower', or null when the text mixes the two. */
function caseOf(text: string): 'upper' | 'lower' | null {
  const hasUpper = /[A-Z]/.test(text);
  const hasLower = /[a-z]/.test(text);
  if (hasUpper && hasLower) {
    return null;
  }
  return hasLower ? 'lower' : 'upper';
}

/** The 8.3 name a long name is filed under, unique within its directory. */
function shortNameFor(name: string, taken: Set<string>): string {
  const cleaned = name
    .toUpperCase()
    .replace(/[^A-Z0-9$%'\-_@~`!(){}^#&.]/g, '_')
    .replace(/^\.+/, '');
  const dot = cleaned.lastIndexOf('.');
  const rawBase = (dot <= 0 ? cleaned : cleaned.slice(0, dot)).replace(/\./g, '_') || 'FILE';
  const ext = (dot <= 0 ? '' : cleaned.slice(dot + 1, dot + 4)).padEnd(3, ' ');

  for (let n = 1; ; n++) {
    const tail = `~${n}`;
    const base = (rawBase.slice(0, Math.max(1, 8 - tail.length)) + tail).padEnd(8, ' ');
    const candidate = base + ext;
    if (!taken.has(candidate)) {
      taken.add(candidate);
      return candidate;
    }
  }
}

function buildEntries(
  name: string,
  taken: Set<string>,
  cluster: number,
  size: number,
  isDirectory: boolean,
): Uint8Array[] {
  const fitted = shortNameOf(name);
  // A name that fits 8.3 is already reserved for it, so only a name that has
  // to be invented has to look at what the directory has taken.
  const short = fitted ? fitted.short : shortNameFor(name, taken);
  const ntFlags = fitted ? fitted.ntFlags : 0;

  const entries: Uint8Array[] = [];
  if (!fitted || fitted.needsLong) {
    const checksum = shortNameChecksum(short);
    const parts = Math.ceil(name.length / LFN_CHARS_PER_ENTRY);
    // Long-name entries sit in front of the short one, last part first.
    for (let part = parts; part >= 1; part--) {
      entries.push(lfnEntry(name, part, part === parts, checksum));
    }
  }
  entries.push(shortEntry(short, cluster, size, isDirectory, ntFlags));
  return entries;
}

function shortEntry(
  short: string,
  cluster: number,
  size: number,
  isDirectory: boolean,
  ntFlags = 0,
): Uint8Array {
  const entry = new Uint8Array(DIR_ENTRY_SIZE);
  const view = new DataView(entry.buffer);
  entry.set(asciiBytes(short), 0);
  entry[11] = isDirectory ? ATTR_DIRECTORY : ATTR_ARCHIVE;
  entry[12] = ntFlags;
  const {date, time} = fatTimestamp();
  view.setUint16(14, time, true);
  view.setUint16(16, date, true);
  view.setUint16(18, date, true);
  view.setUint16(20, (cluster >>> 16) & 0xffff, true);
  view.setUint16(22, time, true);
  view.setUint16(24, date, true);
  view.setUint16(26, cluster & 0xffff, true);
  view.setUint32(28, isDirectory ? 0 : size, true);
  return entry;
}

function dotEntry(name: string, cluster: number): Uint8Array {
  return shortEntry(name.padEnd(11, ' '), cluster, 0, true);
}

function lfnEntry(name: string, part: number, isLast: boolean, checksum: number): Uint8Array {
  const entry = new Uint8Array(DIR_ENTRY_SIZE);
  const view = new DataView(entry.buffer);
  entry[0] = part | (isLast ? LFN_LAST_MASK : 0);
  entry[11] = ATTR_LFN;
  entry[13] = checksum;
  const offsets = [1, 3, 5, 7, 9, 14, 16, 18, 20, 22, 24, 28, 30];
  const start = (part - 1) * LFN_CHARS_PER_ENTRY;
  for (let i = 0; i < offsets.length; i++) {
    const index = start + i;
    const code = index < name.length ? name.charCodeAt(index) : index === name.length ? 0 : 0xffff;
    view.setUint16(offsets[i], code, true);
  }
  return entry;
}

function shortNameChecksum(short: string): number {
  let sum = 0;
  for (let i = 0; i < 11; i++) {
    sum = (((sum & 1) << 7) + (sum >> 1) + short.charCodeAt(i)) & 0xff;
  }
  return sum;
}

function fatTimestamp(): {date: number; time: number} {
  const now = new Date();
  const date = ((now.getFullYear() - 1980) << 9) | ((now.getMonth() + 1) << 5) | now.getDate();
  const time = (now.getHours() << 11) | (now.getMinutes() << 5) | (now.getSeconds() >> 1);
  return {date, time};
}

function asciiBytes(text: string): Uint8Array {
  const bytes = new Uint8Array(text.length);
  for (let i = 0; i < text.length; i++) {
    bytes[i] = text.charCodeAt(i) & 0xff;
  }
  return bytes;
}
