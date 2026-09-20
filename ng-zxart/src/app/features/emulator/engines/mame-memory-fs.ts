/**
 * Keeps an Emularity-loaded emulator's filesystem in memory.
 *
 * The loader mirrors every file it mounts into IndexedDB and then **refuses to
 * overwrite one it already has**, so the first file ever mounted under a name
 * is the one every later play gets — two releases holding a `game.trd` would
 * run the same disk, and a card built per release would never be the one that
 * was built. A card is also tens of megabytes a play that nothing reads back,
 * so the store grows until the browser refuses writes and a start hangs with
 * the file half-written.
 *
 * Saying the backend is unavailable keeps the whole filesystem in memory,
 * where an emulator that is thrown away when its dialog closes belongs.
 */

interface BrowserFsGlobals {
  BrowserFS?: {FileSystem?: {IndexedDB?: {isAvailable: () => boolean}}};
}

/**
 * @param staleStores IndexedDB databases earlier versions left behind, deleted
 *                    on the way past so they stop taking up the user's quota.
 */
export async function useInMemoryFileSystem(staleStores: string[] = []): Promise<void> {
  const indexedDbBackend = (window as unknown as BrowserFsGlobals).BrowserFS?.FileSystem?.IndexedDB;
  if (indexedDbBackend) {
    indexedDbBackend.isAvailable = () => false;
  }

  if (!window.indexedDB) {
    return;
  }
  for (const store of staleStores) {
    await new Promise<void>(resolve => {
      const request = indexedDB.deleteDatabase(store);
      // A delete blocked by another tab leaves that tab's store behind, which
      // is no worse than not having tried.
      request.onsuccess = request.onerror = request.onblocked = () => resolve();
    });
  }
}
