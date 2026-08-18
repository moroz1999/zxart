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
