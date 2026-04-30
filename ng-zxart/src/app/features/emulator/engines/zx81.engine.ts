import {EmulatorEngine, EmulatorType} from './emulator-engine';

export class Zx81Engine implements EmulatorEngine {
  readonly type: EmulatorType = 'zx81';

  start(): Promise<void> {
    return Promise.reject(new Error('Zx81Engine: not implemented yet'));
  }

  setFullscreen(): void {}

  destroy(): void {}
}
