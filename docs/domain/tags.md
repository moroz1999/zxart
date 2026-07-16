## Tags

Tags are `tag` structure elements linked to items (pictures, music, prods) via
`tagLink` links (tag = link parent, item = link child). Each tag stores a global
usage `amount` (count of connected items), recalculated on `module_tag`.

### Tag cloud

The graphics and music sections each expose a tag cloud as a standalone SPA route:

- `/pictures/tags` (section `graphics`) and `/music/tags` (section `music`), served
  by `pages/tags` → `zx-tags-cloud` (`features/tags-list/`).
- Each tag links to the matching search entrypoint with the tag pre-applied:
  `/pictures/search?tagsInclude=<title>` / `/music/search?tagsInclude=<title>`.
  Font size scales with the tag's `amount`.

### API

- Endpoint: `GET /tags-list-data/?section=graphics|music` (`ZxArt\Controllers\TagsListData`). Spec: `api/tags-list.yaml`.
- `TagsListService` gets the section's tag ids from `TagsListRepository` (a
  structure-link query: tag ids whose `tagLink` children are rows of the section's
  items table), then loads the tag elements to read language-resolved titles and
  amounts. Tags with `amount = 0` are omitted; the list is ordered by title.
