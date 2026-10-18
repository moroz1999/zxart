export interface PixelSize {
  readonly width: number;
  readonly height: number;
}

/**
 * Scale for showing a pixel-art image on a stage: the largest whole multiple of
 * its native size that fits the stage less `margin` on every side, so each
 * source pixel stays a sharp square. An image larger than the stage at 1× is
 * shrunk just enough to fit instead. Returns 0 while the size is unknown.
 */
export function integerFitScale(native: PixelSize, stage: PixelSize, margin: number): number {
  if (native.width <= 0 || native.height <= 0) {
    return 0;
  }
  const fit = Math.min(
    (stage.width - 2 * margin) / native.width,
    (stage.height - 2 * margin) / native.height,
  );
  if (fit < 1) {
    return Math.max(fit, 0);
  }
  return Math.floor(fit);
}
