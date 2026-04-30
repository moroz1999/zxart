import {EmulatorEngine, EmulatorType} from './emulator-engine';

export class SamcoupeEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'samcoupe';

  start(): Promise<void> {
    return Promise.reject(new Error('SamcoupeEngine: not implemented yet'));
  }

  setFullscreen(): void {}

  destroy(): void {}
}
