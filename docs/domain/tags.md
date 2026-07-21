## Tags

Tags are `tag` structure elements linked to items (pictures, music, prods) via
`tagLink` links (tag = link parent, item = link child). Usage amounts are derived
from these links for the requested item section; `module_tag` stores tag data but
does not store a shared usage counter.

### Tag cloud

The graphics, music, and software sections each expose a tag cloud as a
standalone SPA route:

- `/pictures/tags` (section `graphics`), `/music/tags` (section `music`), and
  `/prods/tags` (section `software`), served by `pages/tags` → `zx-tags-cloud`
  (`features/tags-list/`).
- The cloud can show all used tags or only tags used by at least 3, 5, or 10
  items in the current section. The default minimum is 10. The initial request uses a
  tag-cloud skeleton; filter-driven reloads preserve and dim the displayed cloud.
- Each tag links to the matching search entrypoint with the tag pre-applied:
  `/pictures/search?tagsInclude=<title>`, `/music/search?tagsInclude=<title>`, or
  `/prods?tags=<id>`. Font size scales with the tag's `amount`.

### API

- Endpoint: `GET /tags-list-data/?section=graphics|music|software&minimumAmount=3`
  (`ZxArt\Controllers\TagsListData`). `minimumAmount` is optional and defaults
  to 10. Spec: `api/tags-list.yaml`.
- `TagsListRepository` joins `tagLink` children to the requested section's item
  table, groups them by tag, and applies the minimum to that section-specific
  count. `TagsListService` loads the matching tag elements for language-resolved
  titles. The list is ordered by title.
