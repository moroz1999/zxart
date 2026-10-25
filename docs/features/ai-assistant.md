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

Tests: `tests/Assistant/Tools/` (schema, request building, registry, each tool
with its service mocked) and `tests/Integration/Assistant/ToolCallTest.php` (a
call resolved from the container and built from JSON).
