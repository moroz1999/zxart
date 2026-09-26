import {EmulatorError} from './emulator-error';
import {EmulatorStartOptions, EmulatorType} from './emulator-engine';
import {MameBoot, MameEngine} from './mame.engine';
import {MameMachine, SAMCOUPE_MACHINE} from './mame-machine';
import {extensionOf} from './release-files';

/**
 * The SAM Coupé mounts a file MAME opens directly and needs no card: SAMDOS
 * boots off the disk, which is what the machine's autoboot keystrokes say.
 *
 * The disk is mounted under a name carrying its format and nothing else —
 * every floppy format here is picked from the extension, and a published name
 * may hold spaces, brackets or a second dot.
 */
export class SamcoupeEngine extends MameEngine {
  readonly type: EmulatorType = 'samcoupe';
  protected readonly machine: MameMachine = SAMCOUPE_MACHINE;

  private canvas: HTMLCanvasElement | null = null;
  private readonly pointerLockHandler = () => {
    void this.canvas?.requestPointerLock();
  };

  override async start(
    canvas: HTMLCanvasElement,
    fileUrl: string,
    container: HTMLElement,
    options: EmulatorStartOptions = {},
  ): Promise<void> {
    this.canvas = canvas;
    // The SAM's own mouse is driven by the pointer, which the browser only
    // hands over on a click inside the picture.
    canvas.addEventListener('click', this.pointerLockHandler);
    await super.start(canvas, fileUrl, container, options);
  }

  override destroy(): void {
    this.canvas?.removeEventListener('click', this.pointerLockHandler);
    this.canvas = null;
    super.destroy();
  }

  protected async prepare(fileUrl: string, options: EmulatorStartOptions): Promise<MameBoot> {
    options.onStatus?.('emulator.status.downloading');
    const response = await fetch(fileUrl);
    if (!response.ok) {
      throw new EmulatorError('emulator.error.download', {status: response.status});
    }
    const name = decodeURIComponent(new URL(fileUrl, window.location.origin).pathname.split('/').pop() ?? '');
    const disk = `release.${extensionOf(name)}`;

    return {
      mounted: [{path: disk, data: new Uint8Array(await response.arrayBuffer())}],
      args: ['-flop1', disk],
    };
  }
}
