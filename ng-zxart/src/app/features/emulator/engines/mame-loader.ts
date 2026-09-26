/**
 * The MAME runtime's own loader (`htdocs/libs/mame/mame-loader.js`): it fetches
 * the files a machine opens, puts them in the Emscripten filesystem and starts
 * the core.
 *
 * It keeps a versioned IndexedDB cache of everything it downloads, so the ROM
 * archives are paid for once per visitor rather than once per play. A file the
 * page already holds is handed over as a `blob:` URL, and the loader leaves
 * those out of the cache — a card built for one release is a name nothing can
 * ask for twice.
 */

/** One file the machine finds in its filesystem, and where it is fetched from. */
export interface MameFile {
  /** Where to fetch it: a URL under the runtime directory, or a `blob:` URL. */
  url: string;
  /** Where MAME looks for it, relative to the working directory. */
  path: string;
}

export interface MameStartOptions {
  canvas: HTMLCanvasElement;
  driver: string;
  /**
   * Where the core itself is. The loader defaults to `mame.js` beside the
   * page, which on a site with routes is not where the runtime lives.
   */
  emulatorJS?: string;
  files: MameFile[];
  args: string[];
  /** Which directory MAME reads its config from, when the machine has one. */
  cfgDir?: string | null;
  /** Which of the saved NVRAM directories the machine comes up in. */
  nvramDir?: string | null;
  /** Creates the `diff` directory a read-only CHD needs to be opened writable. */
  diffDir?: boolean;
  /** Keeps the loader's idea of the bgfx chain in step with `-bgfx_screen_chains`. */
  bgfxInitialChain?: string | null;
  /** Names the versioned IndexedDB store downloads are cached in. */
  cache?: string;
  onProgress?: (fraction: number, name: string) => void;
}

/** What the loader hands back once the machine is starting. */
export interface MameInstance {
  softReset(): void;
  setMute(state: boolean): boolean;
  mute(): boolean;
  unmute(): boolean;
  setShowFps(state: boolean): boolean;
  setBgfxChain(name: string): Promise<string | false>;
  /** The machine's own picture, in its own pixels, or null before it is up. */
  visibleArea(): {width: number; height: number} | null;
  /** Resizes the SDL backing once the window exists; a no-op until then. */
  resizeBacking(width: number, height: number): void;
}

interface MameLoaderStatic {
  start(options: MameStartOptions): MameInstance;
}

declare global {
  interface Window {
    MAMELoader?: MameLoaderStatic;
  }
}

export const MAME_LIB_BASE = '/libs/mame';

const LOADER_URL = `${MAME_LIB_BASE}/mame-loader.js`;

/**
 * The IndexedDB store an earlier runtime mirrored the Next's mounted files
 * into. Nothing writes it any more, but a browser that played a Next release
 * back then is still holding it — and a card is tens of megabytes.
 */
const LEGACY_STORES = ['zxart-zxnext'];

let loaderScript: Promise<MameLoaderStatic> | null = null;

/** Loads the runtime's loader, once per page however many machines are played. */
export function loadMameLoader(): Promise<MameLoaderStatic> {
  loaderScript ??= new Promise<MameLoaderStatic>((resolve, reject) => {
    dropLegacyStores();
    const script = document.createElement('script');
    script.src = LOADER_URL;
    script.onload = () => {
      const loader = window.MAMELoader;
      loader ? resolve(loader) : reject(new Error('MAMELoader is not available'));
    };
    script.onerror = () => reject(new Error(`Failed to load ${LOADER_URL}`));
    document.body.appendChild(script);
  });
  return loaderScript;
}

/**
 * Names bytes the page holds so the loader can fetch them like any other file.
 * Every URL made here is revoked once the machine has read it.
 */
export function blobUrl(data: Uint8Array): string {
  return URL.createObjectURL(new Blob([data as unknown as BlobPart]));
}

/** Gives back the quota an earlier runtime's filesystem store is still taking. */
function dropLegacyStores(): void {
  if (!window.indexedDB) {
    return;
  }
  for (const store of LEGACY_STORES) {
    // A delete blocked by another tab leaves that tab's store behind, which is
    // no worse than not having tried.
    indexedDB.deleteDatabase(store);
  }
}
