import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, firstValueFrom} from 'rxjs';

interface TapeAudioState {
  readonly sourceUrl: string | null;
  readonly loadingUrl: string | null;
  readonly playing: boolean;
}

interface TapeBlock {
  readonly data: Uint8Array;
  readonly pilotPulses: number;
  readonly pilotPulseLength: number;
  readonly syncPulseLengths: readonly number[];
  readonly zeroPulseLength: number;
  readonly onePulseLength: number;
  readonly usedBitsInLastByte: number;
  readonly pauseMs: number;
}

@Injectable({providedIn: 'root'})
export class TapeAudioService {
  private static readonly sampleRate = 44100;
  private static readonly cpuClock = 3500000;
  private static readonly lowLevel = -600;
  private static readonly highLevel = 15000;
  private static readonly tapPilotPulseLength = 2168;
  private static readonly tapSyncPulseLengths = [667, 735] as const;
  private static readonly tapZeroPulseLength = 855;
  private static readonly tapOnePulseLength = 1710;
  private static readonly tapPauseMs = 1500;
  private static readonly headerPilotPulses = 8063;
  private static readonly dataPilotPulses = 3223;

  private readonly store = new BehaviorSubject<TapeAudioState>({
    sourceUrl: null,
    loadingUrl: null,
    playing: false,
  });

  readonly state$ = this.store.asObservable();

  private audio: HTMLAudioElement | null = null;
  private objectUrl: string | null = null;

  constructor(private readonly http: HttpClient) {}

  async toggle(sourceUrl: string, format: string): Promise<void> {
    const state = this.store.getValue();

    if (state.sourceUrl === sourceUrl && state.playing) {
      this.pause();
      return;
    }

    await this.play(sourceUrl, format);
  }

  pause(): void {
    this.audio?.pause();
    this.store.next({...this.store.getValue(), playing: false, loadingUrl: null});
  }

  isPlayableFormat(format: string): boolean {
    return ['tap', 'tzx'].includes(format.toLowerCase());
  }

  private async play(sourceUrl: string, format: string): Promise<void> {
    this.store.next({sourceUrl, loadingUrl: sourceUrl, playing: false});

    try {
      const data = new Uint8Array(await firstValueFrom(this.http.get(sourceUrl, {responseType: 'arraybuffer'})));
      const wavBlob = this.createWavBlob(data, format);
      this.replaceAudioUrl(URL.createObjectURL(wavBlob));

      if (!this.audio) {
        this.audio = new Audio();
        this.audio.addEventListener('ended', () => {
          this.store.next({...this.store.getValue(), playing: false});
        });
      }

      this.audio.src = this.objectUrl ?? '';
      await this.audio.play();
      this.store.next({sourceUrl, loadingUrl: null, playing: true});
      this.trackTapeRun();
    } catch (error) {
      this.store.next({sourceUrl: null, loadingUrl: null, playing: false});
      console.error('Unable to play tape audio', error);
    }
  }

  private createWavBlob(data: Uint8Array, format: string): Blob {
    const blocks = format.toLowerCase() === 'tzx' ? this.parseTzx(data) : this.parseTap(data);
    const samples: number[] = [];

    for (const block of blocks) {
      this.recordPulses(samples, block.pilotPulseLength, block.pilotPulses);
      for (const pulseLength of block.syncPulseLengths) {
        this.recordPulse(samples, pulseLength);
      }
      this.recordData(samples, block);

      if (block.pauseMs > 0) {
        this.recordPause(samples, block.pauseMs);
      }
    }

    return this.bufferToWave(samples);
  }

  private parseTap(data: Uint8Array): TapeBlock[] {
    const blocks: TapeBlock[] = [];
    let offset = 0;

    while (offset + 2 <= data.length) {
      const length = this.readUint16(data, offset);
      offset += 2;

      if (length <= 0 || offset + length > data.length) {
        break;
      }

      const blockData = data.slice(offset, offset + length);
      offset += length;
      blocks.push(this.createStandardBlock(blockData, TapeAudioService.tapPauseMs));
    }

    return blocks;
  }

  private parseTzx(data: Uint8Array): TapeBlock[] {
    if (!this.hasTzxHeader(data)) {
      return this.parseTap(data);
    }

    const blocks: TapeBlock[] = [];
    let offset = 10;

    while (offset < data.length) {
      const blockId = data[offset];
      offset += 1;

      switch (blockId) {
        case 0x10: {
          if (offset + 4 > data.length) return blocks;
          const pauseMs = this.readUint16(data, offset);
          const length = this.readUint16(data, offset + 2);
          offset += 4;
          if (offset + length > data.length) return blocks;
          blocks.push(this.createStandardBlock(data.slice(offset, offset + length), pauseMs));
          offset += length;
          break;
        }
        case 0x11: {
          if (offset + 18 > data.length) return blocks;
          const pilotPulseLength = this.readUint16(data, offset);
          const syncOne = this.readUint16(data, offset + 2);
          const syncTwo = this.readUint16(data, offset + 4);
          const zeroPulseLength = this.readUint16(data, offset + 6);
          const onePulseLength = this.readUint16(data, offset + 8);
          const pilotPulses = this.readUint16(data, offset + 10);
          const usedBitsInLastByte = data[offset + 12] || 8;
          const pauseMs = this.readUint16(data, offset + 13);
          const length = this.readUint24(data, offset + 15);
          offset += 18;
          if (offset + length > data.length) return blocks;
          blocks.push({
            data: data.slice(offset, offset + length),
            pilotPulses,
            pilotPulseLength,
            syncPulseLengths: [syncOne, syncTwo],
            zeroPulseLength,
            onePulseLength,
            usedBitsInLastByte,
            pauseMs,
          });
          offset += length;
          break;
        }
        case 0x14: {
          if (offset + 10 > data.length) return blocks;
          const zeroPulseLength = this.readUint16(data, offset);
          const onePulseLength = this.readUint16(data, offset + 2);
          const usedBitsInLastByte = data[offset + 4] || 8;
          const pauseMs = this.readUint16(data, offset + 5);
          const length = this.readUint24(data, offset + 7);
          offset += 10;
          if (offset + length > data.length) return blocks;
          blocks.push({
            data: data.slice(offset, offset + length),
            pilotPulses: 0,
            pilotPulseLength: TapeAudioService.tapPilotPulseLength,
            syncPulseLengths: [],
            zeroPulseLength,
            onePulseLength,
            usedBitsInLastByte,
            pauseMs,
          });
          offset += length;
          break;
        }
        case 0x20:
          offset += 2;
          break;
        case 0x21:
        case 0x30:
          offset += offset < data.length ? data[offset] + 1 : 0;
          break;
        case 0x22:
          break;
        default:
          return blocks;
      }
    }

    return blocks;
  }

  private createStandardBlock(data: Uint8Array, pauseMs: number): TapeBlock {
    return {
      data,
      pilotPulses: data[0] === 0 ? TapeAudioService.headerPilotPulses : TapeAudioService.dataPilotPulses,
      pilotPulseLength: TapeAudioService.tapPilotPulseLength,
      syncPulseLengths: TapeAudioService.tapSyncPulseLengths,
      zeroPulseLength: TapeAudioService.tapZeroPulseLength,
      onePulseLength: TapeAudioService.tapOnePulseLength,
      usedBitsInLastByte: 8,
      pauseMs,
    };
  }

  private recordData(samples: number[], block: TapeBlock): void {
    for (let byteIndex = 0; byteIndex < block.data.length; byteIndex += 1) {
      const bits = byteIndex === block.data.length - 1 ? block.usedBitsInLastByte : 8;

      for (let bit = 7; bit >= 8 - bits; bit -= 1) {
        const pulseLength = block.data[byteIndex] & (1 << bit)
          ? block.onePulseLength
          : block.zeroPulseLength;
        this.recordPulse(samples, pulseLength);
        this.recordPulse(samples, pulseLength);
      }
    }
  }

  private recordPulses(samples: number[], tStates: number, pulses: number): void {
    for (let i = 0; i < pulses; i += 1) {
      this.recordPulse(samples, tStates);
    }
  }

  private recordPulse(samples: number[], tStates: number): void {
    const level = samples.length && samples[samples.length - 1] === TapeAudioService.highLevel
      ? TapeAudioService.lowLevel
      : TapeAudioService.highLevel;
    const length = Math.max(1, Math.round(tStates * TapeAudioService.sampleRate / TapeAudioService.cpuClock));

    for (let i = 0; i < length; i += 1) {
      samples.push(level);
    }
  }

  private recordPause(samples: number[], pauseMs: number): void {
    const length = Math.round(pauseMs * TapeAudioService.sampleRate / 1000);

    for (let i = 0; i < length; i += 1) {
      samples.push(TapeAudioService.lowLevel);
    }
  }

  private bufferToWave(samples: number[]): Blob {
    const length = samples.length * 2 + 44;
    const buffer = new ArrayBuffer(length);
    const view = new DataView(buffer);
    let pos = 0;

    const setUint16 = (value: number): void => {
      view.setUint16(pos, value, true);
      pos += 2;
    };
    const setUint32 = (value: number): void => {
      view.setUint32(pos, value, true);
      pos += 4;
    };

    setUint32(0x46464952);
    setUint32(length - 8);
    setUint32(0x45564157);
    setUint32(0x20746d66);
    setUint32(16);
    setUint16(1);
    setUint16(1);
    setUint32(TapeAudioService.sampleRate);
    setUint32(TapeAudioService.sampleRate * 2);
    setUint16(2);
    setUint16(16);
    setUint32(0x61746164);
    setUint32(length - pos - 4);

    for (const sample of samples) {
      view.setInt16(pos, sample, true);
      pos += 2;
    }

    return new Blob([view], {type: 'audio/wav'});
  }

  private replaceAudioUrl(objectUrl: string): void {
    if (this.objectUrl) {
      URL.revokeObjectURL(this.objectUrl);
    }

    this.objectUrl = objectUrl;
  }

  private hasTzxHeader(data: Uint8Array): boolean {
    return data.length >= 10
      && data[0] === 0x5a
      && data[1] === 0x58
      && data[2] === 0x54
      && data[3] === 0x61
      && data[4] === 0x70
      && data[5] === 0x65
      && data[6] === 0x21
      && data[7] === 0x1a;
  }

  private readUint16(data: Uint8Array, offset: number): number {
    return data[offset] + data[offset + 1] * 256;
  }

  private readUint24(data: Uint8Array, offset: number): number {
    return data[offset] + data[offset + 1] * 256 + data[offset + 2] * 65536;
  }

  private trackTapeRun(): void {
    const tracker = (window as Window & {ym?: (...args: unknown[]) => void}).ym;

    if (tracker) {
      tracker(94686067, 'reachGoal', 'run-tape');
    }
  }
}
