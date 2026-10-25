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
them) and `tests/Integration/Entities/FoundEntitiesTest.php` (the summaries).
