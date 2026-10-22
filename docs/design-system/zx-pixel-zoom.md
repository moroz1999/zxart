# zx-pixel-zoom

A pixel-art image drawn at an exact size, with a hover loupe: the region under
the pointer is magnified, and the wheel changes the magnification.

`ng-zxart/src/app/shared/ui/zx-pixel-zoom/`

## Inputs
- `image` — the image drawn at `width` × `height`, `image-rendering: pixelated`.
- `fullImage` — a larger render the loupe magnifies; `image` when empty.
- `width`, `height` — display size in CSS pixels; nothing is drawn while `width`
  is 0.
- `alt`, `magnification` (3), `lensWidth` (200), `lensHeight` (150).

## Notes
- It wraps `ngx-image-zoom`, which measures the image once, so the loupe is
  rebuilt whenever the size or the image changes.
- The picture page viewer and the fullscreen gallery use it.
