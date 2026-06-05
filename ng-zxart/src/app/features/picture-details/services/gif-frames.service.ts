import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';

export interface GifFrame {
  /** Cumulative (composited) frame rendered as a PNG data URL. */
  readonly dataUrl: string;
  /** Frame delay in milliseconds. */
  readonly delay: number;
}

export interface DecodedGif {
  readonly width: number;
  readonly height: number;
  readonly frames: GifFrame[];
}

/**
 * Decodes an animated GIF into individual frames, the way the legacy
 * `basic.libgif.js` + `component.stagesAnimation.js` did, but reading an
 * ArrayBuffer instead of a binary string and emitting PNG data URLs.
 * Frames are composited cumulatively onto a single canvas (drawing stages
 * are additive), matching legacy behaviour.
 */
@Injectable({providedIn: 'root'})
export class GifFramesService {
  constructor(private readonly http: HttpClient) {}

  decode(url: string): Observable<DecodedGif | null> {
    return this.http.get(url, {responseType: 'arraybuffer'}).pipe(
      map(buffer => this.parse(new Uint8Array(buffer))),
      catchError(() => of(null)),
    );
  }

  private parse(bytes: Uint8Array): DecodedGif | null {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    if (!ctx) {
      return null;
    }

    const stream = new GifStream(bytes);
    const frames: GifFrame[] = [];

    let width = 0;
    let height = 0;
    let gct: number[][] | null = null;
    let transparency: number | null = null;
    let delay = 0;
    let started = false;

    const pushFrame = () => {
      if (!started) {
        return;
      }
      frames.push({dataUrl: canvas.toDataURL(), delay: delay * 10});
    };

    // Header
    const sig = stream.readString(3);
    stream.readString(3); // version
    if (sig !== 'GIF') {
      return null;
    }
    width = stream.readUnsigned();
    height = stream.readUnsigned();
    canvas.width = width;
    canvas.height = height;

    const packed = stream.readByte();
    const gctFlag = (packed & 0x80) !== 0;
    const gctSize = packed & 0x07;
    stream.readByte(); // bgColor
    stream.readByte(); // pixelAspectRatio
    if (gctFlag) {
      gct = this.readColorTable(stream, 1 << (gctSize + 1));
    }

    // Blocks
    for (;;) {
      const sentinel = stream.readByte();
      const marker = String.fromCharCode(sentinel);

      if (marker === ';') {
        // EOF
        pushFrame();
        break;
      }

      if (marker === '!') {
        const label = stream.readByte();
        if (label === 0xf9) {
          // Graphic Control Extension
          pushFrame();
          stream.readByte(); // block size (always 4)
          const flags = stream.readByte();
          transparency = (flags & 0x01) !== 0 ? -2 : null; // resolved below
          delay = stream.readUnsigned();
          const transparencyIndex = stream.readByte();
          transparency = (flags & 0x01) !== 0 ? transparencyIndex : null;
          stream.readByte(); // terminator
        } else {
          // Skip unknown extension sub-blocks
          this.skipSubBlocks(stream);
        }
        continue;
      }

      if (marker === ',') {
        // Image descriptor
        const leftPos = stream.readUnsigned();
        const topPos = stream.readUnsigned();
        const imgWidth = stream.readUnsigned();
        const imgHeight = stream.readUnsigned();

        const imgPacked = stream.readByte();
        const lctFlag = (imgPacked & 0x80) !== 0;
        const interlaced = (imgPacked & 0x40) !== 0;
        const lctSize = imgPacked & 0x07;

        const lct = lctFlag ? this.readColorTable(stream, 1 << (lctSize + 1)) : null;
        const colorTable = lct ?? gct;

        const minCodeSize = stream.readByte();
        const lzwData = this.readSubBlocks(stream);
        let pixels = lzwDecode(minCodeSize, lzwData);
        if (interlaced) {
          pixels = deinterlace(pixels, imgWidth);
        }

        if (!colorTable) {
          continue;
        }

        started = true;
        const region = ctx.getImageData(leftPos, topPos, imgWidth, imgHeight);
        for (let i = 0; i < pixels.length; i++) {
          const pixel = pixels[i];
          if (pixel === transparency) {
            continue;
          }
          const color = colorTable[pixel];
          if (!color) {
            continue;
          }
          region.data[i * 4 + 0] = color[0];
          region.data[i * 4 + 1] = color[1];
          region.data[i * 4 + 2] = color[2];
          region.data[i * 4 + 3] = 255;
        }
        ctx.putImageData(region, leftPos, topPos);
        continue;
      }

      // Unknown block — abort to avoid an infinite loop.
      break;
    }

    if (!frames.length) {
      return null;
    }
    return {width, height, frames};
  }

  private readColorTable(stream: GifStream, entries: number): number[][] {
    const table: number[][] = [];
    for (let i = 0; i < entries; i++) {
      table.push([stream.readByte(), stream.readByte(), stream.readByte()]);
    }
    return table;
  }

  private readSubBlocks(stream: GifStream): number[] {
    const data: number[] = [];
    let size = stream.readByte();
    while (size !== 0) {
      for (let i = 0; i < size; i++) {
        data.push(stream.readByte());
      }
      size = stream.readByte();
    }
    return data;
  }

  private skipSubBlocks(stream: GifStream): void {
    let size = stream.readByte();
    while (size !== 0) {
      stream.skip(size);
      size = stream.readByte();
    }
  }
}

class GifStream {
  private pos = 0;

  constructor(private readonly data: Uint8Array) {}

  readByte(): number {
    if (this.pos >= this.data.length) {
      throw new Error('Attempted to read past end of GIF stream.');
    }
    return this.data[this.pos++];
  }

  readUnsigned(): number {
    const a = this.readByte();
    const b = this.readByte();
    return (b << 8) + a;
  }

  readString(n: number): string {
    let s = '';
    for (let i = 0; i < n; i++) {
      s += String.fromCharCode(this.readByte());
    }
    return s;
  }

  skip(n: number): void {
    this.pos += n;
  }
}

function lzwDecode(minCodeSize: number, data: number[]): number[] {
  let pos = 0;
  const readCode = (size: number): number => {
    let code = 0;
    for (let i = 0; i < size; i++) {
      if (data[pos >> 3] & (1 << (pos & 7))) {
        code |= 1 << i;
      }
      pos++;
    }
    return code;
  };

  const output: number[] = [];
  const clearCode = 1 << minCodeSize;
  const eoiCode = clearCode + 1;
  let codeSize = minCodeSize + 1;
  let dict: number[][] = [];

  const clear = () => {
    dict = [];
    codeSize = minCodeSize + 1;
    for (let i = 0; i < clearCode; i++) {
      dict[i] = [i];
    }
    dict[clearCode] = [];
    dict[eoiCode] = [];
  };

  let code: number | undefined;
  let last: number | undefined;

  for (;;) {
    last = code;
    code = readCode(codeSize);

    if (code === clearCode) {
      clear();
      continue;
    }
    if (code === eoiCode) {
      break;
    }

    if (code < dict.length) {
      if (last !== clearCode && last !== undefined) {
        dict.push(dict[last].concat(dict[code][0]));
      }
    } else {
      if (code !== dict.length || last === undefined) {
        throw new Error('Invalid LZW code.');
      }
      dict.push(dict[last].concat(dict[last][0]));
    }

    for (const value of dict[code]) {
      output.push(value);
    }

    if (dict.length === (1 << codeSize) && codeSize < 12) {
      codeSize++;
    }
  }

  return output;
}

function deinterlace(pixels: number[], width: number): number[] {
  const newPixels = new Array<number>(pixels.length);
  const rows = pixels.length / width;
  const offsets = [0, 4, 2, 1];
  const steps = [8, 8, 4, 2];
  let fromRow = 0;
  for (let pass = 0; pass < 4; pass++) {
    for (let toRow = offsets[pass]; toRow < rows; toRow += steps[pass]) {
      const fromPixels = pixels.slice(fromRow * width, (fromRow + 1) * width);
      for (let i = 0; i < width; i++) {
        newPixels[toRow * width + i] = fromPixels[i];
      }
      fromRow++;
    }
  }
  return newPixels;
}
