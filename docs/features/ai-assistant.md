# AI assistant — implementation

The plan for the whole feature is in [../chat/README.md](../chat/README.md).
This document describes what exists.

## Tools

`ZxArt\Assistant\Tools` holds what the assistant calls:

- `Tool` — `name()` (snake case), `description()` (for the model),
  `requestClass()` (the request DTO), `execute(request)` (a result DTO).
  `ReadTool` and `WriteTool` extend it separately, so a listing of read tools
  never holds a write; a `WriteTool` adds `preview(request): ChangePreviewDto`.
- `ToolRegistry` — the tools by name, in the order given; two tools of one name
  are refused, an unknown name is an `UnknownToolException`. The tools are
  listed in `di-definitions.php`.
- `JsonSchemaGenerator::forClass()` — the JSON Schema of a request DTO from its
  constructor: the types the request deserializer reads (docblock first, then
  the native type), `@param` descriptions, enums as their values, nested DTOs
  and lists; a parameter without a default is required, and no other property
  is allowed. A type with no schema (`mixed`, an untyped array) is a
  `LogicException`.
- `ToolRequestBuilder::build()` — the request DTO from a call's JSON arguments,
  through the REST request serializer. Not JSON, not an object, a top-level
  field the DTO does not have, a missing or mistyped field are a
  `ToolArgumentsException` whose message names the field, for the model.
- `Dto\ChangePreviewDto` — what a plan shows for a write: `ChangeKind`, the
  target (`ChangeTargetDto`: type, id or the step that creates it, title), the
  changed fields (`FieldChangeDto`: before and after, an entity value as an
  `EntityMentionDto`), consequences in sentences, and the target's
  modification time.

Read tools so far:

| Tool | Class | Service |
|---|---|---|
| `search` | `Search\SearchTool` | `EntitySearchService` (see [search.md](search.md#entity-phrase-search)); `types` is `SearchableType`, exactly the six types |
| `find_locations` | `Search\FindLocationsTool` | `PlaceSearchService` |
| `search_prods` | `Search\SearchProdsTool` | `ProdSearchService`, then `FoundProdsReader` |
| `search_releases` | `Search\SearchReleasesTool` | `ReleaseSearchService`, then `FoundReleasesReader` |
| `search_authors` | `Search\SearchAuthorsTool` | `AuthorSearchService`, then `FoundAuthorsReader` |
| `search_groups` | `Search\SearchGroupsTool` | `GroupSearchService`, then `FoundGroupsReader` |
| `find_tags` | `Catalogues\FindTagsTool` | `Tags\Dbal\TagSearchDbal` (see [search.md](search.md#typed-searches)) |
| `list_categories` | `Catalogues\ListCategoriesTool` | `CategoryManageService::getTree()`: id, parent, level, title, productions of the subtree |
| `list_hardware` | `Catalogues\ListHardwareTool` | `HardwareCatalogService`: codes by group, named in the current language |
| `list_options` | `Catalogues\ListOptionsTool` | `LegalStatus`, `ReleaseTypes`, `ReleaseFormatsProvider`, `LanguageCodes`, `GroupTypes`, `AuthorshipRoles`, `GroupMemberRoles` |
| `get_prod`, `get_release`, `get_author`, `get_author_alias`, `get_group`, `get_group_alias` | `Entities\Get<Entity>Tool` | `<Entity>CardReader` in the entity's module |
| `fetch_url` | `Web\FetchUrlTool` | `Shared\Http\PageFetcher` |
| `view_image` | `Web\ViewImageTool` | `Shared\Images\RemoteImageReader` (`url`), `StoredImageReader` (`fileId`) |

The `get_*` tools take `GetEntityRequestDto` (`id`) and answer a card
(`<Entity>CardDto`) built for an edit:

- `fields` — the entity's `<Entity>FieldsDto` from its fields reader, the very
  shape its writes take;
- `refs` — `EntityRefDto` of every entity the fields name by id (party,
  categories, groups, publishers, series and compilation items, production,
  country, city, subgroups, the owner of an alias), from `EntityRefsReader`,
  which leaves out an id of nothing;
- production: credited authors (`CreditDto`), its releases as
  `FoundReleaseDto`, the files of every list (`ListedFileDto`: kind, id, file
  name, from `ListedFilesReader`), the series and compilations it is part of;
- release: credited authors, files, the release file's name and size, the
  hardware and languages of its production;
- author and group: aliases with dates (`AliasDto`), memberships from their own
  side (`MembershipDto`: the other side's ref, roles, dates, from
  `MembershipsReader`) — an alias's are on its own card — and the counters the
  public page shows (`AuthorDetailsService::getCounters()`,
  `GroupDetailsService::getCounters()`); a group also has the groups it is a
  subgroup of.

Links that other entities make to the read one (series, compilations, parent
groups) are read from `structure_links` through `StructureLinksDbal`, not from
the element, which holds what it loaded.

`fetch_url` answers `FetchedPageDto`: the final URL after redirects, the
status (any — an error page is read too), the Content-Type header as it came,
the body and its size. The body is converted to UTF-8 from the charset of the
header, or for HTML of its `<meta>`, and cut at `PageFetcher::MAX_BYTES` (200
KB) without breaking a character, `truncated` then set. A response whose type
is not text, JSON, XML or JavaScript has no body, only its type and size.
`view_image` takes `url` or `fileId` (one of them, else
`ToolArgumentsException`) and answers `ImageDto` (media type, base64 data) for
the LLM client to pass as image input: a PNG, JPEG, GIF or WebP of up to 5 MB,
the type read from the bytes (`ImageBytes`); anything else is
`ImageRefusedException`. A `fileId` is a `fileElement` read from its path on
disk.

Both go through `Shared\Http\GuardedHttpClient`, which `GuardedDownloader`
uses too: `http`/`https` only, every address — the first and each redirect, at
most 5 — passes `UrlGuard` (`PublicAddressGuard`), the request is pinned to
the checked address, the bodies of redirects are dropped and the body of the
last response goes to a consumer that can stop the transfer at a limit. A
refused address or a failed transfer is `HttpRequestRefusedException`.

A tool without input has an empty request DTO; its schema's `properties` is
an empty object.

The four typed searches take `{filter, limit, offset}`: `filter` is the
service's own filter DTO (see [search.md](search.md#typed-searches)), so the
schema the model sees is the one the search reads; `limit` is kept between 1
and 100. They answer the total and one page of summaries, in the order the
search found them. A summary (`Found<Entity>Dto`, read by
`Found<Entities>Reader` in the entity's module) names related entities as
`Shared\References\EntityRefDto` (type, id, title, from `EntityRefs::of()`)
and credits as `Shared\Dto\CreditDto` (the author or alias ref and the
roles, from `CreditsReader`):

- production: year, authors with roles, groups that made it, publishers,
  categories;
- release: production, year, type, formats, file name, authors with roles,
  publishers;
- author: real name, country, city, aliases, groups;
- group: abbreviation, type, country, city, aliases.

Tests: `tests/Assistant/Tools/` (schema, request building, registry, each tool
with its services mocked), `tests/Integration/Assistant/ToolCallTest.php` (a
call resolved from the container and built from JSON, a nested filter among
them) `tests/Integration/Entities/FoundEntitiesTest.php` (the summaries) and
`tests/Integration/Entities/EntityCardsTest.php` (the cards);
`tests/Shared/Http/` and `tests/Shared/Images/` fetch from PHP's built-in
server (`tests/Doubles/Http/LocalFileServer`) behind `LoopbackUrlGuard`.
