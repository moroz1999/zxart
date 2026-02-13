# Picture Gallery (Lightbox + Zoom) - Implementation Specification

## Status
- Last updated: 2026-02-13
- Owner: Codex
- Document type: implementation plan + notes

## Goal
Implement a reusable picture gallery for Angular `zx-picture` cards with:
- lightbox open on image click
- page-scoped playlist navigation
- optional hover zoom mode
- thumbnail navigation inside the lightbox

## Current State (Verified)
- Picture cards already exist: `ng-zxart/src/app/shared/ui/zx-picture-card/`.
- First page picture modules render `zx-picture-card` lists:
  - `new-pictures`
  - `best-pictures-of-month`
  - `unvoted-pictures`
  - `random-good-pictures`
- `ZxPictureDto` currently has only one image field: `imageUrl`.
- There is no dedicated gallery feature/service yet.

## Scope
### In scope (Phase 1)
- Integrate gallery behavior into Angular picture cards.
- Build playlist from currently rendered picture list in a module/page context.
- Add zoom toggle in lightbox (`OFF` by default).
- Add thumbnail strip for quick navigation.

### Out of scope (Phase 1)
- Cross-page/global playlist.
- New fullscreen viewer separate from lightbox.
- New metadata panels not already shown in card/detail UI.

## Required Dependencies
Install in `ng-zxart`:
- `ng-gallery`
- `ng-gallery/lightbox`
- `ngx-image-zoom`

Register providers/modules per official package docs.

## Data Contract and Mapping
### Gallery item model (frontend)
- `id: number`
- `title: string`
- `thumbUrl: string`
- `largeUrl: string`
- `detailsUrl: string`

### Backend/API impact
Current `ZxPictureDto` has only `imageUrl`, so large image quality cannot be guaranteed.

Required update:
- Extend picture DTO used by first-page endpoints with an additional field for large image, for example:
  - `imageLargeUrl` (preferred)

Compatibility rule:
- If `imageLargeUrl` is missing for old payloads, fallback to `imageUrl`.

## User Interaction Requirements
1. User clicks a picture image in `zx-picture-card`.
2. Lightbox opens at the clicked picture index.
3. Next/Previous moves inside the same local playlist.
4. Thumbnail click jumps to the selected image.
5. Zoom toggle in header switches hover zoom:
   - `OFF`: regular image
   - `ON`: `ngx-image-zoom` mode
6. Close behavior uses lightbox defaults (overlay/close button/Esc if supported by lib).

## Angular Architecture (FSD-aligned)
### Feature
Create `ng-zxart/src/app/features/picture-gallery/`:
- `services/picture-gallery.service.ts`
- `models/gallery-picture-item.ts`
- `components/picture-lightbox/` (wrapper if custom layout is required)

Responsibilities:
- build/open playlist
- track current index
- manage zoom toggle state
- sync thumbnail selection

### Shared UI integration
Update `zx-picture-card` to emit open-gallery action instead of plain link-only behavior for image click.

Important:
- Keep title/details link navigation intact.
- Only image click should open gallery.

### Mapping
Add mapper utilities near picture DTO models to convert `ZxPictureDto -> GalleryPictureItem`.

## Implementation Plan
1. Add gallery dependencies and verify app compile.
2. Introduce gallery item model and mapper with fallback (`largeUrl ?? thumbUrl`).
3. Implement `picture-gallery` feature service (open, close, next, prev, goTo, zoom toggle).
4. Integrate with `zx-picture-card` and picture list modules to pass local ordered playlist and start index.
5. Add thumbnail strip and active-item synchronization.
6. Add i18n keys (`en/ru/es`) for gallery controls (zoom, close, next, previous).
7. Extend backend/DTO to provide large image URL; update frontend interface accordingly.
8. Update API docs if DTO contract changes.
9. Run `composer run build` and resolve all Angular build issues.

## Testing and Verification
### Manual checks
- Click each picture module card image -> lightbox opens correctly.
- Open from item N -> active slide is N.
- Next/Previous order equals visible list order.
- Thumbnail click changes active slide.
- Zoom toggle switches behavior and remains stable while navigating.
- Detail links still navigate to picture page.

### Minimal automated coverage
- Mapper unit test: handles `imageLargeUrl` and fallback to `imageUrl`.
- Gallery service unit test: index navigation and zoom state transitions.

## Risks and Mitigations
- Risk: No large image URL from backend.
  - Mitigation: explicit DTO extension + fallback logic.
- Risk: Card click conflicts with existing anchor navigation.
  - Mitigation: bind gallery open only to image area and prevent default only there.
- Risk: Lightbox/theming mismatch with project styles.
  - Mitigation: keep styles minimal and theme-token based; validate in both theme modes.

## Notes
- Keep implementation simple; do not add custom keyboard shortcuts if library defaults are sufficient.
- Do not introduce global shared playlist state in Phase 1.
- Reuse existing picture ordering from currently rendered list.

## Acceptance Criteria
1. Clicking picture image opens lightbox at the clicked index.
2. Navigation stays inside the local page/module picture playlist.
3. Zoom toggle exists, default is `OFF`, and `ON` enables hover zoom.
4. Thumbnail strip is visible and usable.
5. Build passes after integration: `composer run build`.
