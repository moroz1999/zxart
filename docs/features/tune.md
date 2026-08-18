# Tunes — implementation

Domain rules: [../domain/tune.md](../domain/tune.md).

## Collection filters

`ZxArt\Tunes\MusicCollectionFilter` maps a top-music URL segment to one of three
criteria, resolved in `TunesRepository::applyCollectionFilter()`:

- a tag element id to require (`cover`) or to exclude (`original`)
- a set of `module_zxmusic.formatGroup` values (the sound types)
- a `CategoryIds` case whose whole subtree the tune's production must be filed
  under (`games`, `demos`, `press`)

The subtree is expanded by `ProdCategoryTreeService` before the repository sees
it, so the filter matches every category beneath the section — a megademo's
soundtrack counts as demoscene. The roots are the three top-level sections
(`CategoryIds::GAMES`, `DEMOSCENE`, `PRESS`), the same ids the software menu
links to; the leaves inside them (`DEMOS`, `MEGADEMO`, `PRESS_MAGAZINES`, …) are
for assignment only and must never be a filter root.

A tune whose `prod` points at a `zxRelease` rather than a `zxProd` matches none
of the three: the category link hangs on the parent production. 32 tunes are in
that position.

Adding a subset means one enum case plus its `top-music.filter.<case>`
translation in all three i18n bundles.

## Music list endpoint

`GET /musiclist/` (`ZxArt\Controllers\Musiclist`):

- `filter` with `limit` — a named subset of the whole collection; no catalogue
  root is involved.
- `limit` without `filter` — a paged slice of the element named by `elementId`,
  through `linkType` (`structure` or `tagLink`). Without `elementId` the
  `musicCatalogue` root is resolved by structure type.
- `action=related&tuneId=…` — related tunes, with `kind` selecting the relation
  (`author`, `tags`, `tracker`).

## Format group

`formatGroup` options come from `getFormatGroups()` on the element through the
`enums` map of `/formdata/`, as a `clientLabels` enum, so the labels are the
SPA's own `player.formatGroup.*` translations. The conversion service overrides
the stored value for the families it recognises itself (TS, SAA, digital AY, FM).
`/tune-details/` exposes it as `formatGroup`; the file format is exposed as
`format`.

## Prod link

The production a tune belongs to is the `prod` field, linked through `gameLink`
by `ZxArtItem::updateProdLink()`. The link type keeps its historical name; the
column does not.

## Page metadata

`/tune-details/` carries `metadata` like every other entity core response,
resolved by `PageMetadataService::getForPath('/tune/<id>')`. The Angular page
applies it through `PageMetadataService.applyEntityMetadata()` and makes no
metadata request of its own. `og:audio` is emitted only when an MP3 exists.
