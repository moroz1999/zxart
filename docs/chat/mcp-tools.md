# AI assistant — tool catalogue plan

Overview: [README.md](README.md). Run loop and plans: [backend.md](backend.md).
Services the tools stand on: [service-changes.md](service-changes.md).

## Shape of a tool

A tool is a class in `ZxArt\Assistant\Tools\<Group>\` implementing one of two
interfaces:

- `ReadTool`: `name()`, `description()`, `requestClass()`, `execute(request): result`.
  The agent calls it freely; it never writes.
- `WriteTool`: the same, plus `preview(request): ChangePreviewDto`. It is
  callable **only** as an action of an approved plan.

Rules:

- Input is a readonly request DTO. Its **JSON Schema is generated** from the DTO
  (property types, enums, docblock descriptions), so the schema the model sees
  and the deserialization the tool gets cannot drift apart. The serializer from
  [rest-api.md](../php/rest-api.md) builds the DTO; a malformed call is reported
  back to the model as a tool error naming the field.
- Output is a readonly result DTO, serialized to JSON for the model. Results
  contain ids and titles, never legacy URLs.
- A tool is a thin adapter: it calls a domain service and maps its DTOs. Logic,
  privilege checks and the actions log live in the service, so the SPA and the
  assistant share them.
- `ToolRegistry` lists the tools and resolves them by name. It knows nothing of
  the chat: a future public MCP endpoint is a JSON-RPC adapter over the same
  registry plus authentication.
- Every tool runs as the current user ([backend.md](backend.md#running-as-the-user)).

### Change preview

`preview()` returns what the plan message shows for the action:

- `kind`: `create`, `update`, `delete`, `join`, `convert`, `link`, `unlink`, `attachFile`, `deleteFile`;
- `target`: the entity ref (`type`, `id`, `title`), or "created in step N";
- `changes`: rows of `field`, `before`, `after` — only fields that change;
  values that are entities are refs, so the panel can link them;
- `consequences`: plain sentences the user must see — "3 releases move to
  [[prod:12]]", "the author's 40 works lose this author";
- `modifiedAt`: the target's modification time, for the staleness check.

`execute()` returns the ids it produced (`id`, and for joins the surviving id),
which later actions may reference.

## Control tools

Handled by the runner itself, not by the registry.

| Tool | Input | Effect |
|---|---|---|
| `ask_user` | `question`, optional `options[]` | Ends the turn with a question message; options become quick replies |
| `propose_plan` | `title`, `summary`, `actions[]` of `{tool, arguments}` | Validates, previews, shows the plan; ends the turn |

## Read tools

### Search

| Tool | Input | Returns |
|---|---|---|
| `search` | `phrase`, optional `types[]` (`prod`, `release`, `author`, `authorAlias`, `group`, `groupAlias`), `limit` | Matches across types, closest first: type, id, title, year, a short qualifier (author's real name and country, prod's year and groups, release's prod) |
| `search_prods` | `filter` with any of: `title`, `categoryIds[]`, `yearFrom`, `yearTo`, `releaseYearFrom`, `releaseYearTo`, `addedFrom`, `addedTo`, `hardware[]`, `languages[]`, `legalStatuses[]`, `releaseTypes[]`, `formats[]`, `authorIds[]` (all credited, or any with `anyAuthor`), `authorRoles[]`, `groupIds[]`, `groupLink` (`made`, `published`), `publisherIds[]` (authors or groups), `countryIds[]`, `cityIds[]`, `partyIds[]`, `compo`, `maxPartyPlace`, `seriesId`, `compilationId`, `includedProdId`, `tagIds[]`; `limit`, `offset` | Prods with year, authors, groups, categories; total count |
| `search_authors` | `filter` with any of: `name` (nickname, real name or alias), `countryIds[]`, `cityIds[]`, `groupIds[]`, `activeFrom`, `activeTo` (years of the software they made or published), `prodIds[]`, `prodRoles[]`, `prodLink` (`made`, `published`), `prods` (a nested `search_prods` filter), `coAuthorId`; `limit`, `offset` | Authors with real name, location, aliases, groups; total count |
| `search_groups` | `filter` with any of: `name` (title, abbreviation or alias), `countryIds[]`, `cityIds[]`, `types[]`, `foundedFrom`, `foundedTo`, `endedFrom`, `endedTo`, `activeFrom`, `activeTo` (years of their software), `memberIds[]`, `prodIds[]`, `prodLink` (`made`, `published`), `prods` (a nested `search_prods` filter), `parentGroupId`, `subGroupId`; `limit`, `offset` | Groups with location, type, aliases; total count |
| `search_releases` | `filter` with any of: `title`, `fileName`, `md5`, `prodIds[]`, `prods` (a nested `search_prods` filter), `yearFrom`, `yearTo`, `releaseTypes[]`, `formats[]`, `hardware[]`, `languages[]`, `publisherIds[]` (authors or groups), `authorIds[]` (all credited, or any with `anyAuthor`), `authorRoles[]`; `limit`, `offset` | Releases with prod, year, type, format, file name; total count |
| `find_locations` | `phrase` | Countries and cities with ids — the step behind "Alexander + Tallinn" |

"Alexander + Tallinn" is `find_locations("Tallinn")` then
`search_authors(filter: {name: "Alexander", cityIds: […]})`. "Game, Star Wars, 1983–1986" is
`search_prods(filter: {title: "Star Wars", categoryIds: [games], yearFrom: 1983, yearTo: 1986})`.
"Who wrote music for Voronezh games of 1987" is
`search_authors(filter: {prodRoles: ["music"], prods: {categoryIds: [games], yearFrom: 1987, yearTo: 1987, cityIds: […]}})`.

### Entities

| Tool | Returns |
|---|---|
| `get_prod(id)` | All editable fields; authors with roles; groups; publishers; categories; tags; hardware; languages; party, compo, place; series and compilations; releases (id, title, year, type, format); external links |
| `get_release(id)` | All editable fields; prod; authors with roles; publishers; hardware and languages (own and inherited); file name and size; screenshots and other files (id, name) |
| `get_author(id)` | All editable fields; aliases with dates; group memberships with roles and dates; counts of prods, releases, pictures, tunes; external links |
| `get_author_alias(id)` | Fields; its author; group memberships; counts of works |
| `get_group(id)` | All editable fields; members with roles and dates; parent groups and subgroups; aliases; counts of prods and published releases |
| `get_group_alias(id)` | Fields; its group; members; counts |

### Catalogues

| Tool | Returns |
|---|---|
| `list_categories` | The category tree with ids and names |
| `list_hardware` | Hardware codes with names, by group |
| `list_options` | Fixed value lists: legal statuses, release types, release formats, languages, group types, author roles in prods, member roles in groups |
| `find_tags(phrase)` | Tags by a part of the name or a synonym in any language, the most used first: id, title, synonyms, how many works carry it |

### Outside the site

| Tool | Input | Returns |
|---|---|---|
| `fetch_url` | `url` | Final URL after redirects, status, content type, and the response body as it came, unchanged (HTML stays HTML), so the model reads the page's own structure. Truncated at a size limit with a note |
| `view_image` | `url`, or `fileId` of a file on the site | The image itself, passed to the model as image input |

`fetch_url` rules: `http`/`https` only; the resolved address must not be
private, loopback or link-local (checked after every redirect); a size and time
limit; the declared charset is converted to UTF-8; no JavaScript is run.
Binary responses return only type and size — files are downloaded by write
tools, never by the agent.

## Write tools

Each takes the ids and fields shown; every field of an `update_*` is optional
and only the fields given change ("patch"). List-valued fields (categories,
hardware, languages, groups, publishers) are **replaced as a whole** when given;
the preview shows exactly what is added and removed.

### Productions

| Tool | Input |
|---|---|
| `create_prod` | `title`, optional `altTitle`, `year`, `description`, `htmlDescription`, `instructions`, `legalStatus`, `externalLink`, `youtubeId`, `languages[]`, `hardware[]`, `categoryIds[]`, `groupIds[]`, `publisherIds[]`, `tags[]`, `compilationItemIds[]`, `seriesProdIds[]` |
| `update_prod` | `id` + any field of `create_prod` |
| `delete_prod` | `id` |
| `join_prods` | `mainId`, `joinedId`, `releasesOnly` — the joined prod's releases and data move to the main one, the joined one is deleted |
| `set_prod_author` | `prodId`, `authorId` (author or alias), `roles[]` — adds the author or replaces their roles |
| `remove_prod_author` | `prodId`, `authorId` |

### Releases

| Tool | Input |
|---|---|
| `create_release` | `prodId`, `title`, optional `year`, `version`, `releaseType`, `releaseFormat[]`, `hardware[]`, `languages[]`, `publisherIds[]`, `description`, `fileUrl` |
| `update_release` | `id` + any field of `create_release` except `fileUrl`; `prodId` moves the release |
| `delete_release` | `id` |
| `set_release_file` | `releaseId`, `url`, optional `fileName` — the site downloads the file and makes it the release's file; format and hardware are derived from it as for an upload |
| `add_files` | `entityType` (`prod`/`release`), `id`, `kind` (prod: `screenshots`, `inlays`, `maps`, `rzx`; release: `screenshots`, `inlays`, `info`, `ads`), `urls[]` |
| `delete_file` | `entityType`, `id`, `fileId` |
| `set_release_author` / `remove_release_author` | as for prods |

### Authors and aliases

| Tool | Input |
|---|---|
| `create_author` | `title`, optional `realName`, `countryId`, `cityId`, `wikiLink`, `site` |
| `update_author` | `id` + any field of `create_author` |
| `delete_author` | `id` |
| `join_authors` | `mainId`, `joinedId`, `asAlias` — merge into the main author, or keep the joined one as its alias |
| `convert_author_to_group` | `id` |
| `create_author_alias` | `authorId`, `title`, optional `startDate`, `endDate` |
| `update_author_alias` | `id` + `title`, `startDate`, `endDate`, `authorId` |
| `delete_author_alias` | `id` |
| `convert_alias_to_author` | `id` |

### Groups and aliases

| Tool | Input |
|---|---|
| `create_group` | `title`, optional `abbreviation`, `type`, `countryId`, `cityId`, `website`, `wikiLink`, `slogan`, `startDate`, `endDate`, `parentGroupIds[]` |
| `update_group` | `id` + any field of `create_group` |
| `delete_group` | `id` |
| `join_groups` | `mainId`, `joinedId`, `asAlias` |
| `convert_group_to_author` | `id` |
| `create_group_alias` | `groupId`, `title`, optional `startDate`, `endDate` |
| `update_group_alias` | `id` + `title`, `startDate`, `endDate`, `groupId` |
| `delete_group_alias` | `id` |
| `set_group_member` | `groupId` (group or alias), `authorId` (author or alias), `roles[]`, optional `startDate`, `endDate` — adds or updates the membership |
| `remove_group_member` | `groupId`, `authorId` |

Not in the first version: splitting a production, screenshot ordering, import
ids, tags as entities, parties, pictures, tunes, press.

## Checklist

- [x] `ReadTool` / `WriteTool` interfaces, `ChangePreviewDto`, `ToolRegistry`, `ToolRequestBuilder` — see [features/ai-assistant.md](../features/ai-assistant.md#tools)
- [x] JSON Schema generation from request DTOs (+ tests on representative DTOs)
- [x] Search tools on the services from [service-changes.md](service-changes.md) — see [features/ai-assistant.md](../features/ai-assistant.md#tools)
- [ ] `get_*` tools for the six entity types
- [x] Catalogue tools, `find_tags`
- [ ] `fetch_url` with address guard and limits, body returned as is; `view_image`
- [ ] Write tools for prods, releases, authors, author aliases, groups, group aliases — each with `preview()` and `execute()`
- [ ] Tests per tool: request mapping, preview rows, refused privilege
