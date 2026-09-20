/**
 * Turns an SCL disk into the TRD MAME can open.
 *
 * MAME's Beta Disk floppy reads TRD and rejects an SCL outright — the machine
 * never starts, which is the black screen an SCL release showed. The two hold
 * the same TR-DOS disk: an SCL is the catalogue and the files back to back,
 * with no free space and no geometry, so laying them out on an empty 80-track
 * double-sided disk is the whole conversion.
 */

const SECTOR_SIZE = 256;
const SECTORS_PER_TRACK = 16;
const TRACKS = 160;
const TRD_SIZE = TRACKS * SECTORS_PER_TRACK * SECTOR_SIZE;
/** The catalogue is track 0 sectors 0-7; files start at track 1. */
const FIRST_FILE_SECTOR = SECTORS_PER_TRACK;
const DATA_SECTORS = (TRACKS - 1) * SECTORS_PER_TRACK;
/** TR-DOS keeps the disk's own record at the end of track 0 sector 8. */
const DISK_INFO = 0x8e0;
/** 80 tracks, both sides. */
const DISK_TYPE_80_DS = 0x16;
const TRDOS_ID = 0x10;

const SIGNATURE = 'SINCLAIR';
/** An SCL catalogue entry is a TR-DOS one without its place on the disk. */
const SCL_ENTRY_SIZE = 14;
const TRD_ENTRY_SIZE = 16;
const MAX_FILES = 128;
/** The 4 bytes of checksum the archive ends with. */
const CHECKSUM_SIZE = 4;

export function isScl(data: Uint8Array): boolean {
  if (data.length < 9) {
    return false;
  }
  for (let i = 0; i < SIGNATURE.length; i++) {
    if (data[i] !== SIGNATURE.charCodeAt(i)) {
      return false;
    }
  }
  return true;
}

export function sclToTrd(scl: Uint8Array): Uint8Array {
  const count = scl[8];
  const bodyStart = 9 + count * SCL_ENTRY_SIZE;
  if (!isScl(scl) || count > MAX_FILES || scl.length < bodyStart + CHECKSUM_SIZE) {
    throw new Error('Damaged SCL disk');
  }

  const trd = new Uint8Array(TRD_SIZE);
  let sector = 0; // counted from track 1 sector 0, which is where files start
  for (let i = 0; i < count; i++) {
    const entry = scl.subarray(9 + i * SCL_ENTRY_SIZE, 9 + (i + 1) * SCL_ENTRY_SIZE);
    trd.set(entry, i * TRD_ENTRY_SIZE);
    trd[i * TRD_ENTRY_SIZE + 14] = sector % SECTORS_PER_TRACK;
    trd[i * TRD_ENTRY_SIZE + 15] = 1 + Math.floor(sector / SECTORS_PER_TRACK);
    // The last byte of the entry is the file's length in sectors, and the
    // files follow each other with nothing between them.
    sector += entry[13];
  }

  const body = scl.subarray(bodyStart, scl.length - CHECKSUM_SIZE);
  if (sector > DATA_SECTORS || body.length > DATA_SECTORS * SECTOR_SIZE) {
    throw new Error('The SCL disk holds more than a TR-DOS disk can');
  }
  trd.set(body, FIRST_FILE_SECTOR * SECTOR_SIZE);

  trd[DISK_INFO + 1] = sector % SECTORS_PER_TRACK;
  trd[DISK_INFO + 2] = 1 + Math.floor(sector / SECTORS_PER_TRACK);
  trd[DISK_INFO + 3] = DISK_TYPE_80_DS;
  trd[DISK_INFO + 4] = count;
  const free = DATA_SECTORS - sector;
  trd[DISK_INFO + 5] = free & 0xff;
  trd[DISK_INFO + 6] = (free >> 8) & 0xff;
  trd[DISK_INFO + 7] = TRDOS_ID;
  // TR-DOS writes spaces here and a disk label eight characters long.
  trd.fill(0x20, DISK_INFO + 10, DISK_INFO + 18);
  for (let i = 0; i < 8; i++) {
    trd[DISK_INFO + 21 + i] = 'ZXART   '.charCodeAt(i);
  }

  return trd;
}
