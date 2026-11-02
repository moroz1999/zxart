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

The `get_*` tools take `Dto\EntityIdRequestDto` (`id`) and answer a card
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
disk; a ZX Spectrum screen (`fileElement::getZxScreenType()` names its
converter type by the extension) is rendered in process by
`ZxScreen\ZxScreenRenderer` — the `ZxImage\Converter` the `zximages`
controller uses, colours mixed, sRGB, zoom 2, no border — to a PNG, or a GIF
for a flickering screen.

Both go through `Shared\Http\GuardedHttpClient`, which `GuardedDownloader`
uses too: `http`/`https` only, every address — the first and each redirect, at
most 5 — passes `UrlGuard` (`PublicAddressGuard`), the request is pinned to
the checked address, the bodies of redirects are dropped and the body of the
last response goes to a consumer that can stop the transfer at a limit. A
refused address or a failed transfer is `HttpRequestRefusedException`.

Write tools, in `Prods\`, `Releases\`, `Authors\`, `AuthorAliases\`,
`Groups\`, `GroupAliases\`:

| Entity | Tools |
|---|---|
| prod | `create_prod`, `update_prod`, `delete_prod`, `join_prods`, `set_prod_author`, `remove_prod_author`, `add_prod_files`, `delete_prod_file` |
| release | `create_release`, `update_release`, `delete_release`, `set_release_author`, `remove_release_author`, `add_release_files`, `delete_release_file`, `set_release_file` |
| author | `create_author`, `update_author`, `delete_author`, `join_authors`, `convert_author_to_group` |
| author alias | `create_author_alias`, `update_author_alias`, `delete_author_alias`, `join_author_aliases`, `convert_author_alias_to_author` |
| group | `create_group`, `update_group`, `delete_group`, `join_groups`, `convert_group_to_author`, `set_group_member`, `remove_group_member` |
| group alias | `create_group_alias`, `update_group_alias`, `delete_group_alias`, `join_group_aliases`, `convert_group_alias_to_group`, `set_group_alias_member`, `remove_group_alias_member` |

`preview()` reads the state through `<Entity>StateService` and answers what
would change; it does not check privileges or input rules, so an action the
handler would refuse is previewed and fails when it runs. `execute()` builds the
command of its use case, calls `handle()` (see
[php/entity-writes.md](../php/entity-writes.md#use-cases)) and answers the handler's result: `{id}` — of the new entity for a
create or a conversion — or, for added files, `AddedFilesDto` (`id`,
`fileIds`).

- `create_*` / `update_*` take the entity's `<Entity>FieldsDto` as `fields`
  (an update also `id`): only the fields given change, a list given replaces
  the list. Their rows come from `Previews\FieldChanges::diff()` — a row per
  given field that differs from the state (every given field for a create).
  The fields of entity ids and of dates are listed per fields DTO in
  `FieldChanges`: ids show mentions (`Previews\Mentions`; an id of nothing
  stays a number, 0 is no value), dates the day (`Previews\Dates`, `Y-m-d`);
  an enum shows its value, every other field as it is (`zxTunesId` is a
  number).
- `delete_*`, `convert_*` take `Dto\EntityIdRequestDto`. A delete's
  consequences name what the entity holds: a production's releases, an
  author's or alias's credits (the public page's counters) and aliases, a
  group's or group alias's members, productions made and publications
  (`Previews\Quantities::phrase()`). A conversion says the entity is deleted
  and what the new one of the same title takes.
- `join_*` (`mainId`, `joinedId`; `releasesOnly` for productions, `asAlias` for
  authors and groups) previews the joined entity's deletion and what moves,
  as sentences with `[[type:id]]` mentions (`EntityMentionDto::text()`).
- `set_*_author` / `set_*_member` and their `remove_*` show the author coming
  or going, its roles and, for group members, the dates
  (`Previews\CreditChanges`). Removing someone not credited is refused at the
  preview (`404`).
- `add_*_files` (`kind` of the entity's file kinds, `urls`) shows the list
  with the URLs appended; `delete_*_file` the list without the file;
  `set_release_file` (`url`, optional `fileName`) the release file's name
  before and after.

The modification time in a preview is the target's (the main entity's for a
join); a create has none.

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
`tests/Integration/Assistant/*WriteToolsTest.php` (previews and writes of
the write tools, refusals when they run, over `WriteToolTestCase`); `tests/Shared/Http/` and
`tests/Shared/Images/` fetch from PHP's built-in
server (`tests/Doubles/Http/LocalFileServer`) behind `LoopbackUrlGuard`.

## Chat

### Access

`aiAssistant` is a site-wide privilege on the public root, like
`editHardware` (declared in `root/structure.actions.php`). The migration
`2026.11.01 - ai assistant.sql` grants it to the group `ai-assistant-users`.
Membership is managed in the admin panel; a new member sees the assistant after
their next login, because privileges are read from the session.

### Storage

InnoDB tables, one migration; deleting a conversation cascades to its rows.
Each table has a Dbal class in `Assistant\Chat\Dbal`:

| Table | Dbal | Holds |
|---|---|---|
| `ai_conversation` | `ConversationsDbal` | owner, title (the first message, cut to 80 characters), last activity |
| `ai_message` | `MessagesDbal` | `seq` per conversation, `role`, `kind` (`MessageKind`), text, JSON payload |
| `ai_run` | `RunsDbal` | `type` (`chat`, `execute`), `status` (`RunStatus`), `stopRequested`, steps, tokens, error |
| `ai_plan` | `PlansDbal` | title, `status` (`PlanStatus`), its actions as one JSON column |

`MessagesDbal::append()` gives a message the conversation's next `seq`; the
client polls for everything after the last `seq` it holds.

A plan action as stored: `tool`, `arguments` (the JSON as written),
`preview` (the normalized `ChangePreviewDto`, or null), `dependsOn` (the
actions it takes ids from), `status` (`PlanActionStatus`), `result`, `error`.

### The model

`Assistant\Llm\LlmClient` is a chat model that may answer with tool calls, in
the assistant's own DTOs (`LlmMessageDto`, `LlmToolDto`, `LlmToolCallDto`,
`LlmReplyDto`). `OpenAiLlmClient` implements it over the OpenAI **Responses
API** with function tools (the configured model does not take function tools
over chat completions), `store: false`, so every request carries the whole
conversation. An image is sent as an `input_image` data URL inside a user
message.

Every exchange is logged to `temporary/logs/assistant/<date>.log` (path key
`assistant_logs`), each entry as `[run] : conversation N …`, and only what is
new: `request <model> input[from..to] of count, tools: <names>` with the input
items not logged before — for the first request of a run those after the
model's last turn (what came before is in the log of the earlier runs), for a
later one those added since the previous request of the run; an image as
`<type, size>`. Then `response after N ms` with the raw response — output
items, reasoning, usage — but without its echo of the tools, or
`failed after N ms` with the error. That log is where a run is debugged.

The model is `ai_assistant_model` in the main config, the key is `ai_key`; a
local copy, which has no live config, reads the key from the `OPENAI_API_KEY`
environment variable (`docker-compose.yml` takes it from `.env`).

### A run

`Agent\AgentRunner::run()` answers the conversation:

1. `ConversationHistory` turns the stored messages into the model's: the
   system prompt first; a tool call as the call and its result; a proposed
   plan as the `propose_plan` call; plan progress as a system note; errors left
   out. The page a user wrote from is appended to their message. Images a tool
   showed are not kept.
2. The model is offered every **read** tool, with its request's JSON schema,
   `describe_write_tool` and `propose_plan`. Each reply is stored first — prose as a `text`
   message, each call as a `toolCall` message with its result — then the
   results are fed back. A failed call (unknown tool, bad arguments, a write
   tool called directly, a refusal) is told to the model as `Error: …`, and the
   run goes on.
3. The run ends `done` on a reply without calls, `waitingApproval` once a plan
   is proposed, `stopped` when the user asked it to stop (checked before each
   step, told as a `stopped` message), `failed` after 20 steps, 10 minutes or a
   model failure, with an `error` message.

### Plans

`Agent\PlanProposer` handles `propose_plan` (`title`, `actions` of `tool` and
`arguments`). Its description lists the write tools by name only
(`Agent\WriteToolCatalogue`); the model reads what one does and its argument schema with
`describe_write_tool` before it writes the tool into a plan, so the descriptions and schemas of
some thirty write tools do not ride along with every request.
Each action must name a write tool; `{"$ref": N}` in place of an id takes the
id action N creates, and must point to an earlier action (`PlanReferences`).
An action without references is built and previewed; when its arguments do not
fit the tool or its preview finds nothing to change (an id of nothing), the plan
is refused and the reason goes back to the model. The privileges and rules of
the use case are not checked before the plan runs. An action with
references is not previewed, because what it refers to does not exist yet.

`Agent\PlanExecutor` runs an approved plan: each action through its write tool,
references resolved from the earlier results' `id`, stopping at the first
failure, or before the next action once the run is asked to stop (the rest are
`skipped`, the plan `failed` or `stopped`). Each outcome is saved on the plan
and told as a `planProgress` message. An action proposed without a preview,
because it refers to an earlier one, is previewed right before it runs, with
the ids in place, and that preview is saved on it. Unless it was stopped, a `chat` run
follows at once, so the assistant sums up what came of the plan; a failed
action reaches it with its error, and the prompt tells it to fix the cause and
propose a new plan of the actions left.

### Service and endpoint

`Chat\AssistantChatService` is what the controller calls: a conversation of
another user does not exist (404); one run at a time per conversation (409); a
run still `running` after 15 minutes is taken for dead and failed. `list` gives
the user's conversations with a running flag; `delete` is refused while a run
works on the conversation; `stop` flags its active run. `send`
supersedes a proposed plan and starts a `chat` run; `approvePlan` marks the plan
`executing` and starts an `execute` run; `cancelPlan` ends a proposed plan
`cancelled` and tells it as a `planProgress` message, without a run. `state()` answers the messages after a
`seq`, every plan, the active run and `refs` — the entities that the messages'
`[[type:id]]` and the plans' previews mention, and those the plans' done
actions created (the preview target's type, the result's `id`), found by
`EntityMentionLookup`.

`Controllers\AssistantData` (`/assistant-data/`, see `api/assistant-data.yaml`)
checks the user and the privilege, answers, and for `send` and `approvePlan`
goes on after the response: `Shared\Http\ResponseCloser` releases the session,
closes the connection (`fastcgi_finish_request` where it exists) and lifts the
time limit, then the run continues in the same process.

### Panel

`features/assistant/` in the SPA:

- `AssistantTriggerComponent` — the header button, rendered only for a holder
  of `aiAssistant` (`RootPrivilegeService`).
- `AssistantDrawerComponent` — a fixed drawer on the right, in `spa-root`
  outside the router outlet, so links to entities keep it open. While it exists
  it subscribes to `AssistantStore.polling$`. Its header names the open
  conversation (from the list, reloaded whenever the conversation or its run
  changes) and opens the conversation list
  (`AssistantConversationsComponent`), which then takes the place of the stream
  and the composer. The composer is locked while a run works, and "Stop" takes
  the place of "Send"; under it is the title of the page the next message
  carries.
- `AssistantConversationsComponent` — the conversations grouped into today,
  yesterday and earlier (`groupConversations()`), each a `zx-list-item` with
  its time and a delete button behind a confirm dialog.
- `AssistantActivityComponent` — the tool calls made in a row, folded into one
  line with their count; a step per call in `zx-steps`, named by a phrase per
  tool and what it was about. It is open while the run adds to it; "Details"
  shows each call's JSON.
- `AssistantPlanComponent` — the plan's status chip (the steps done, the step
  it stopped at), a `zx-step` per action: the entity type, the target (a link
  once it exists, also for what the plan created), the kind of change, the
  changed fields, consequences, an error, the step a skipped one waited for.
  An action without a preview shows its arguments instead (its `fields`
  opened), the title its fields give as the target, and an id it takes from an
  earlier step as that step's target with the step's number, a link once the
  step has created it.  An argument named `<type>Id(s)` after an entity type
  names that entity with its title and id (the state's `refs` hold it); an
  image address (png, jpg, gif, webp) shows as a small image.
  While the plan waits: "Run the plan", behind a confirm dialog that counts the
  destructive actions, and "Cancel".
- `AssistantMarkdownComponent` — the assistant's prose: `marked`, loaded in a
  chunk of its own when the store is created, renders it with known mentions as
  links (`createAssistantMarkdown`); Angular sanitizes the HTML as it binds it.
  A click on a link to a page of the site goes through the router; until
  `marked` arrives, prose is shown as text with links (`zx-assistant-text`).
  Every message with a payload, and a plan with its whole record, can be
  expanded to its JSON.
- `AssistantPanelService` — open or closed, remembered per browser.
- `AssistantStore` — the open conversation: a new one when the drawer starts,
  created on the first message; messages merged by `seq`, plans and run
  replaced from each poll; fetches a conversation once when it is opened and
  then polls every 2 s only while a run is active — an idle conversation
  changes only by the user's own send, approve or stop, which mark a run, and
  a cancelled plan is fetched once. `buildAssistantView()` turns it into the
  stream the components draw: messages, and the tool calls between them as
  activities; plan progress notes are left out, the plan shows them.
- `AssistantPageContextService` — the page sent with every message: the URL,
  and the entity type and id of an entity page; and the page's title, for the
  composer.
- `AssistantDataApiService` (`shared/api/`) — the only code that knows the URL.

The components are built from the design system only: layout through
`zx-stack` / `zx-inline` / `zx-inset`, text through `appText` / `appHeading`,
messages in tinted flat `zx-panel`s (`surface` `primary` for the user, `muted`
for the assistant), plans and expanded JSON in flat panels too, steps in
`zx-steps`, statuses and kinds in `zx-chip`s, JSON in `zx-preformatted`,
preview rows in `zx-item-data`, controls as `zx-button`. Their SCSS keeps only
the drawer's contour and scrolling; the drawer's own tokens are in
`shared/theme/_zx-assistant.theme.scss`.

Tests: `tests/Integration/Assistant/Chat/` (storage, the service),
`tests/Integration/Assistant/Agent/` (the run with a scripted model, plans
proposed and executed with real write tools), `tests/Assistant/Agent/`
(history), `tests/Assistant/Llm/` (the OpenAI request and reply over the
client's fake); the specs next to the panel's services.
