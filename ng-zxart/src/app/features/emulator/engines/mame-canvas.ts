/**
 * Makes a MAME machine fill the canvas once its runtime is up.
 *
 * MAME measures the canvas when SDL brings its window up and then leaves it
 * alone, so it draws into whatever shape it happened to find. In Chrome the
 * dialog's box is often not laid out yet at that moment and the picture comes
 * out the wrong size; going fullscreen and back sets it right, and a resize
 * event once the runtime is there is that same nudge.
 *
 * **The size belongs to the caller.** Every machine here runs at a resolution
 * of its own while sharing one global `Module`, so a nudge always carries the
 * size of the engine that asked for it and can never hand another machine its
 * own — which is why this takes the size instead of knowing one.
 */

/** How long to keep waiting for the MAME runtime before giving up on the nudge. */
const SETTLE_INTERVAL_MS = 250;
const SETTLE_TRIES = 240;

/**
 * @param width     the machine's canvas width, as its engine launched MAME with
 * @param height    the machine's canvas height
 * @param isRunning false once the engine has been torn down, which stops the wait
 */
export function settleCanvasSize(
  width: number,
  height: number,
  isRunning: () => boolean,
): void {
  let tries = 0;
  const nudge = () => {
    if (!isRunning()) {
      return; // torn down while waiting
    }
    if (window.Module?.setCanvasSize) {
      window.Module.setCanvasSize(width, height);
      window.dispatchEvent(new Event('resize'));
      return;
    }
    if (++tries < SETTLE_TRIES) {
      setTimeout(nudge, SETTLE_INTERVAL_MS);
    }
  };
  setTimeout(nudge, SETTLE_INTERVAL_MS);
}
