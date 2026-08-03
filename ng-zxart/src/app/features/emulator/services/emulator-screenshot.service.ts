import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {ScreenshotFormat} from '../engines/emulator-engine';

export type UspScreenSelection = '48' | '128' | 'giga';

/** Screen dump formats accepted by the `/screenshot-upload/` endpoint. */
export type UploadScreenshotFormat = ScreenshotFormat | 's80' | 's81';

interface EmscriptenFs {
  readdir(path: string): string[];
  chdir(path: string): void;
  readFile(path: string): Uint8Array;
}

const SCREEN_SIZE = 6912;
const SAVESTATE_HEADER = 27;
const REG_7FFD_OFFSET = SAVESTATE_HEADER + 49152 + 2;
const PRIMARY_SCREEN_OFFSET = SAVESTATE_HEADER;
const SECONDARY_SCREEN_OFFSET_PAGE_7 = SAVESTATE_HEADER + 32768;
const SECONDARY_SCREEN_OFFSET_DEFAULT = SAVESTATE_HEADER + 16384 * 3 + 4 + 16384 * 4;

@Injectable({providedIn: 'root'})
export class EmulatorScreenshotService {
  constructor(private http: HttpClient) {}

  uploadBlob(blob: Blob, elementId: number, format: UploadScreenshotFormat): Observable<unknown> {
    return this.http.post('/screenshot-upload/', blob, {
      params: {id: elementId, format},
    });
  }

  captureAndUpload(
    selection: UspScreenSelection,
    fileUrl: string,
    elementId: number,
  ): Observable<unknown> {
    const blob = this.captureFromFs(selection, fileUrl);
    if (!blob) {
      return throwError(() => new Error('USP screenshot capture failed: emulator FS unavailable'));
    }
    const format: ScreenshotFormat = selection === 'giga' ? 'gigascreen' : 'standard';
    return this.uploadBlob(blob, elementId, format);
  }

  private captureFromFs(selection: UspScreenSelection, fileUrl: string): Blob | null {
    const fs = (window as unknown as {FS?: EmscriptenFs}).FS;
    if (!fs) {
      return null;
    }
    const dir = fileUrl.substring(0, fileUrl.lastIndexOf('/') + 1);
    const entries = fs.readdir(dir);
    const stateFile = entries[2];
    if (!stateFile) {
      return null;
    }
    fs.chdir(dir);
    const contents = fs.readFile(stateFile);
    const reg7ffd = contents[REG_7FFD_OFFSET];
    // Verbatim port of legacy `reg7ffd & 0b111 === 7` (parsed as `reg7ffd & (0b111===7)` = `reg7ffd & 1`).
    // Likely intended page-7 check; preserved bit-for-bit so existing screenshots remain reproducible.
    const secondaryOffset = (reg7ffd & 1)
      ? SECONDARY_SCREEN_OFFSET_PAGE_7
      : SECONDARY_SCREEN_OFFSET_DEFAULT;

    if (selection === '48') {
      return new Blob([contents.slice(PRIMARY_SCREEN_OFFSET, PRIMARY_SCREEN_OFFSET + SCREEN_SIZE)]);
    }
    if (selection === '128') {
      return new Blob([contents.slice(secondaryOffset, secondaryOffset + SCREEN_SIZE)]);
    }
    return new Blob([
      contents.slice(PRIMARY_SCREEN_OFFSET, PRIMARY_SCREEN_OFFSET + SCREEN_SIZE),
      contents.slice(secondaryOffset, secondaryOffset + SCREEN_SIZE),
    ]);
  }
}
