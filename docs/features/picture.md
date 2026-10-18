# Pictures — implementation

Domain rules: [../domain/picture.md](../domain/picture.md).

## Collection filters

`ZxArt\Pictures\PictureCollectionFilter` maps a top-graphics URL segment to
either a tag element id or a list of `module_zxpicture.type` values, and never to
both. `PictureListService::getPagedCollection()` sends the tag ones through the
`tagLink` query and the format ones through
`PicturesRepository::findPagedIdsByFormats()`/`countByFormats()`.

Adding a subset means one enum case plus its `top-pictures.filter.<case>`
translation in all three i18n bundles — the SPA builds the chip strip from the
enum's slugs and needs no other change.

## Picture list endpoint

`GET /picturelist/` (`ZxArt\Controllers\Picturelist`) serves three shapes:

- `limit` present — a paged, sorted slice of the whole collection. `tagId`
  narrows it to one tag; `filter` narrows it to a `PictureCollectionFilter`
  subset instead. Sortable by `title`, `date`, `year`, `votes`; the response is
  `{total, items}`.
- `action=related&pictureId=…` — related pictures, with `kind` selecting the
  relation (`author`, `tags`, `prod`).
- `elementId=…` without `limit` — the pictures of a single element (release,
  party compo, …), optionally narrowed by `compoType`.

Author picture lists and picture view logging use `/pictures-data/`.

## Routing

`/pictures/top` declares `:filter` as a childless child route, so switching a
chip keeps the page component alive. `SpaRouter` matches
`/(pictures|music)/top(/<segment>)?`.

## Edit form

The stored file is a native ZX Spectrum screen, so the `adminImage` preset cannot
render it: the form shows only the picked file's name and takes the thumbnail of
the stored one from `getImageUrl()`. The reference images beside it (`inspired`,
`inspired2`, `sequence`) are ordinary images and preview normally.

The production the picture belongs to is the `prod` field, linked through
`gameLink` by `ZxArtItem::updateProdLink()`. The link type keeps its historical
name; the column does not.

## Fullscreen gallery

`PictureGalleryService.ensureGalleryLoaded()` loads a list's `ZxPictureDto`s into
the ng-gallery lightbox. `mapPictureToGalleryItem()` attaches a
`PictureGalleryInfo`, and `mapGalleryItemToImageItem()` keeps every picture an
ordinary `ImageItem` whose data also carries the info and `zoomSrc`. The item
must stay an image item: the gallery's own `gallery-image` loads it, and its
loading state is what the slider relies on to keep the picture it opened at —
a custom item type made the slider jump back to the first picture on opening.
The host's image template renders `zx-picture-gallery-slide` for items with
info (the zoom overlay alone otherwise); the slide's opaque background covers the
`gallery-image` under it.

The slider renders the neighbouring slides too, and the lightbox focuses its
first focusable element on opening. A slide that is not the active one is
therefore `inert`: focus landing in a neighbour would scroll the slider to it and
switch the gallery away from the picture it was opened at.

The slide lays out the picture block and, across a small gap,
`zx-picture-gallery-info` (title, authors, format badge, `zx-item-controls`) —
below the picture under `md`. The info has no panel of its own: it overrides the
text tokens for the always-black backdrop (`--zx-picture-gallery-on-dark-*`).
Picture galleries switch ng-gallery's own arrows off (`nav: false`); the slide
draws prev/next in the side margins of the picture block and turns the open
gallery through `PictureGalleryService.showNext()`/`showPrevious()`. The
lightbox takes `--zx-lightbox-width` × `--zx-lightbox-height` of the viewport.

The slide shows the zoom-1 render (`imageUrl`), so the loaded image's
`naturalWidth`/`naturalHeight` are the native size. `integerFitScale()` picks the
largest whole multiple that fits the picture block (less the arrow margins) less
a 16 px margin, recomputed on load and on every resize, and the image is drawn
`pixelated`; below 1× it shrinks to fit. The zoom lens magnifies the zoom-2
render (`zoomSrc`). Galleries of other images (screenshots, maps, materials)
keep the stock arrows and show no info.

## Downloads

`PictureDownloadsBuilder` builds the downloads of the details response. Each row
carries a `kind` (`PictureDownloadKind`: `original`, `pc`, `print`, `executable`,
`stages`) instead of a label; the SPA names it through
`picture-details.download-kind.<kind>`. A PC render carries its `zoom`, a
material its uploaded `fileName`.

## Rendered image URLs

A native ZX screen is rendered on request by `ZxArt\Controllers\Zximages`, and
every address it is served at is built by `ZxArt\ZxScreen\ZxPictureUrlHelper`:
`/zximages/id=<file>;border=…;mode=…;pal=…;type=…;zoom=…`. The id is the stored
file, not the picture element.

The same parameters used to be written as path segments under `/zxscreen/`.
`ZxArt\Controllers\Zxscreen` reads them as ordinary request parameters, hands
them to the same helper and answers 301 with the current address, so links and
indexed images from that era keep working.

## Viewer settings

`PictureSettingsService` persists every viewing setting as a user preference
through `UserPreferencesService`.

- Render settings (`picture_mode`, `picture_border`, `picture_hidden`) are
  exposed as `settings` and feed `PictureUrlBuilderService`.
- The detail viewer zoom (`picture_scale`: `1`, `2`, `3`, `wide`) is exposed
  separately as `scale`, because it changes the layout rather than the image URL.

The stored zoom is shared across devices, while the viewer only offers the zooms
that fit the current screen (`SCALES_BY_DEVICE`). A stored value too large for
the screen falls back to `wide` for display without being overwritten.

## Year fallback

`ZxArtItem::updateYear()` is shared by every zx item and runs from
`publicReceive` and the batch upload actions.
