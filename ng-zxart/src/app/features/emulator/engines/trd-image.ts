/**
 * A TR-DOS disk image as MAME insists on seeing one.
 *
 * MAME reads a `.trd` as raw sectors and works the geometry out from the file
 * size, so an image that was trimmed to the sectors it actually uses — which
 * is how plenty of them are published — is refused outright with `Unable to
 * identify image file format`, and the machine never starts. Pac-Man Emulator
 * ships as 28928 bytes of a 655360-byte disk.
 *
 * The disk says what shape it is: byte 0x8E3 of the volume sector names the
 * format, and everything after the last used sector is empty space that was
 * dropped rather than data that is missing.
 */

/** 16 sectors of 256 bytes per track, which every TR-DOS disk has. */
const TRACK_BYTES = 16 * 256;

/** What byte 0x8E3 says the disk is, as tracks and sides. */
const DISK_TYPES: Record<number, number> = {
  0x16: 80 * 2,
  0x17: 40 * 2,
  0x18: 80 * 1,
  0x19: 40 * 1,
};

const DISK_TYPE_OFFSET = 0x8e3;

/** The sizes a TR-DOS disk comes in, smallest first. */
const STANDARD_SIZES = [40, 80, 160].map(tracks => tracks * TRACK_BYTES);

/**
 * The image padded out to the disk it is part of, or itself when it already
 * fills one. An image larger than any standard disk is left alone: it is
 * something this does not understand, and MAME is the better judge of it.
 */
export function padTrdImage(data: Uint8Array): Uint8Array {
  const size = fullSize(data);
  if (size === null || data.length >= size) {
    return data;
  }
  const padded = new Uint8Array(size);
  padded.set(data);
  return padded;
}

/** How big the whole disk is, by what the image says of itself. */
function fullSize(data: Uint8Array): number | null {
  const tracks = data.length > DISK_TYPE_OFFSET ? DISK_TYPES[data[DISK_TYPE_OFFSET]] : undefined;
  if (tracks) {
    return tracks * TRACK_BYTES;
  }
  // No usable type byte: the smallest standard disk it still fits in.
  return STANDARD_SIZES.find(standard => data.length <= standard) ?? null;
}
