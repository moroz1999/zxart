/**
 * The little of ZIP the Next emulator needs: the system tree it boots from and
 * the release it plays both arrive as ZIPs and have to be on the SD card as
 * separate files. Inflating is the browser's own `DecompressionStream`, so
 * this costs no dependency.
 */

/** One file out of an archive, with the slash-separated path it was stored under. */
export interface ArchiveFile {
  path: string;
  data: Uint8Array;
}

const LOCAL_HEADER_SIGNATURE = 0x04034b50;
const CENTRAL_HEADER_SIGNATURE = 0x02014b50;
const END_OF_CENTRAL_DIRECTORY_SIGNATURE = 0x06054b50;
/** The end record carries a comment of at most 64 KB behind it. */
const END_RECORD_SEARCH_LENGTH = 66_000;
const METHOD_STORE = 0;
const METHOD_DEFLATE = 8;
const FLAG_UTF8_NAMES = 0x800;

/**
 * The archive formats a release can arrive in that the browser cannot open.
 * They are named so a release packed in one can say so instead of being staged
 * as a single unreadable blob.
 */
const FOREIGN_ARCHIVES: {name: string; magic: number[]}[] = [
  {name: 'RAR', magic: [0x52, 0x61, 0x72, 0x21]},
  {name: '7z', magic: [0x37, 0x7a, 0xbc, 0xaf]},
  {name: 'gzip', magic: [0x1f, 0x8b]},
  {name: 'bzip2', magic: [0x42, 0x5a, 0x68]},
];

/** The archive format this file is in, when it is one that cannot be opened here. */
export function foreignArchiveName(buffer: ArrayBuffer): string | null {
  const head = new Uint8Array(buffer, 0, Math.min(8, buffer.byteLength));
  for (const {name, magic} of FOREIGN_ARCHIVES) {
    if (magic.every((byte, index) => head[index] === byte)) {
      return name;
    }
  }
  return null;
}

export function isZip(buffer: ArrayBuffer): boolean {
  if (buffer.byteLength < 4) {
    return false;
  }
  return new DataView(buffer).getUint32(0, true) === LOCAL_HEADER_SIGNATURE;
}

/**
 * Every file in the archive, directories and macOS resource forks left out.
 * Throws when the archive is not one this can read — a ZIP64 archive, or one
 * compressed with anything but deflate.
 */
export async function readZipArchive(buffer: ArrayBuffer): Promise<ArchiveFile[]> {
  const view = new DataView(buffer);
  const end = findEndRecord(view);
  const count = view.getUint16(end + 10, true);
  let offset = view.getUint32(end + 16, true);

  const files: ArchiveFile[] = [];
  for (let i = 0; i < count; i++) {
    if (view.getUint32(offset, true) !== CENTRAL_HEADER_SIGNATURE) {
      throw new Error('Damaged ZIP archive');
    }
    const flags = view.getUint16(offset + 8, true);
    const method = view.getUint16(offset + 10, true);
    const compressedSize = view.getUint32(offset + 20, true);
    const uncompressedSize = view.getUint32(offset + 24, true);
    const nameLength = view.getUint16(offset + 28, true);
    const extraLength = view.getUint16(offset + 30, true);
    const commentLength = view.getUint16(offset + 32, true);
    const localOffset = view.getUint32(offset + 42, true);
    const name = decodeName(new Uint8Array(buffer, offset + 46, nameLength), flags);
    offset += 46 + nameLength + extraLength + commentLength;

    if (name.endsWith('/') || name.startsWith('__MACOSX/') || name.split('/').pop() === '.DS_Store') {
      continue;
    }
    if (localOffset === 0xffffffff || compressedSize === 0xffffffff) {
      throw new Error('ZIP64 archives are not supported');
    }

    const dataStart =
      localOffset +
      30 +
      view.getUint16(localOffset + 26, true) +
      view.getUint16(localOffset + 28, true);
    const stored = new Uint8Array(buffer, dataStart, compressedSize);
    files.push({path: name, data: await inflate(stored, method, uncompressedSize)});
  }
  return files;
}

function findEndRecord(view: DataView): number {
  const from = Math.max(0, view.byteLength - END_RECORD_SEARCH_LENGTH);
  for (let offset = view.byteLength - 22; offset >= from; offset--) {
    if (view.getUint32(offset, true) === END_OF_CENTRAL_DIRECTORY_SIGNATURE) {
      return offset;
    }
  }
  throw new Error('Not a ZIP archive');
}

function decodeName(bytes: Uint8Array, flags: number): string {
  // Without the UTF-8 flag the name is in the archiver's own code page. Latin-1
  // keeps every byte a character, which is what the card then files it under.
  const encoding = (flags & FLAG_UTF8_NAMES) !== 0 ? 'utf-8' : 'windows-1252';
  return new TextDecoder(encoding).decode(bytes).replace(/\\/g, '/');
}

async function inflate(data: Uint8Array, method: number, size: number): Promise<Uint8Array> {
  if (method === METHOD_STORE) {
    return data.slice();
  }
  if (method !== METHOD_DEFLATE) {
    throw new Error(`Unsupported ZIP compression method ${method}`);
  }
  const stream = new Blob([data]).stream().pipeThrough(new DecompressionStream('deflate-raw'));
  const inflated = new Uint8Array(await new Response(stream).arrayBuffer());
  if (inflated.length !== size) {
    throw new Error('Damaged ZIP entry');
  }
  return inflated;
}
