/**
 * The release as an emulator that mounts it whole has to see it: a list of
 * files with the paths they were published under.
 *
 * ZXArt serves the release file exactly as uploaded, so unpacking is the
 * browser's job. Only ZIP is opened here; an archive in any other format is
 * refused by name rather than staged as one unreadable blob.
 */

import {ArchiveFile, foreignArchiveName, isZip, readZipArchive} from './zip-archive';
import {EmulatorError} from './emulator-error';

export async function fetchReleaseFiles(
  fileUrl: string,
  launchFilePath?: string,
): Promise<ArchiveFile[]> {
  const response = await fetch(fileUrl);
  if (!response.ok) {
    throw new EmulatorError('emulator.error.download', {status: response.status});
  }
  const buffer = await response.arrayBuffer();
  if (isZip(buffer)) {
    return readZipArchive(buffer);
  }
  const foreign = foreignArchiveName(buffer);
  if (foreign) {
    throw new EmulatorError('emulator.error.archive-format', {format: foreign});
  }
  // A release that is one file IS the launch file, so it takes the name the
  // release knows it by rather than whatever the URL happens to end with.
  const name = launchFilePath || decodeURIComponent(fileUrl.split('/').pop() ?? 'release');
  return [{path: name, data: new Uint8Array(buffer)}];
}

/** The lower-case extension of a path, without the dot. */
export function extensionOf(path: string): string {
  return path.split('.').pop()?.toLowerCase() ?? '';
}
