import {EmulatorEngine, EmulatorType} from './emulator-engine';

export class ZxNextEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'zxnext';

  start(): Promise<void> {
    return Promise.reject(new Error('ZxNextEngine: not implemented yet'));
  }

  setFullscreen(): void {}

  destroy(): void {}
}
