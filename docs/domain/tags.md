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
  items in the current section. The default minimum is 3. The initial request uses a
  tag-cloud skeleton; filter-driven reloads preserve and dim the displayed cloud.
- Each tag links by ID to its catalogue-specific browser page:
  `/pictures/tags/{id}`, `/music/tags/{id}`, or `/prods/tags/{id}`. Font size
  scales with the tag's `amount`.
- Selected graphics tags use the picture card grid, music tags use the tune
  table, and software tags use the prod browser. These pages support sorting by
  name, rating, and date added.
- Tag chips on picture, tune, and prod detail pages build the same
  catalogue-specific SPA routes from the tag ID. A tag is resolved under a
  different menu for each catalogue.
- Selected tag pages use localized item wording followed by the quoted tag, for
  example `Pictures tagged "Example"`. The same heading is used in the browser
  title and description metadata, both in the server SPA shell and after Angular
  navigation.

### API

- Endpoint: `GET /tags-list-data/?section=graphics|music|software&minimumAmount=3`
  (`ZxArt\Controllers\TagsListData`). `minimumAmount` is optional and defaults
  to 3. Spec: `api/tags-list.yaml`.
- `TagsListRepository` joins `tagLink` children to the requested section's item
  table, groups them by tag, and applies the minimum to that section-specific
  count. `TagsListService` loads the matching tag elements for language-resolved
  titles. The list is ordered by title.
- Endpoint: `GET /tag-details/?id={tagId}&section=graphics|music|software`
  (`ZxArt\Controllers\TagDetails`) returns the localized selected-tag heading
  and page metadata. The tag must be available under the requested section.
