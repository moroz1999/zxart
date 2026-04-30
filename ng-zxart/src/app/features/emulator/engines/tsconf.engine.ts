import {EmulatorEngine, EmulatorType} from './emulator-engine';

export class TsconfEngine implements EmulatorEngine {
  readonly type: EmulatorType = 'tsconf';

  start(): Promise<void> {
    return Promise.reject(new Error('TsconfEngine: not implemented yet'));
  }

  setFullscreen(): void {}

  destroy(): void {}
}
