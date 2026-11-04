# AI assistant — implementation

The plan for the whole feature is in [../chat/README.md](../chat/README.md).
This document describes what exists.

## Tools

Everything the assistant calls is an action (see [Actions](#actions)).
`Actions\ToolRegistry` holds them by name, in the order given; two of one name
are refused, an unknown name is an `UnknownToolException`. They are listed in
`di-definitions.php`.

Read actions besides the entity reads:

| Action | Class | Service |
|---|---|---|
| `search` | `Search\SearchAction` | `EntitySearchService` (see [search.md](search.md#entity-phrase-search)); `types` is `SearchableType`, exactly the six types |
| `find_locations` | `Search\FindLocationsAction` | `PlaceSearchService` |
| `find_parties` | `Search\FindPartiesAction` | `Parties\Dbal\PartySearchDbal` (see [search.md](search.md#typed-searches)) |
| `search_prods` | `Prods\SearchProdsAction` | `ProdSearchService`, then `FoundProdsReader` |
| `search_releases` | `Releases\SearchReleasesAction` | `ReleaseSearchService`, then `FoundReleasesReader` |
| `search_authors` | `Authors\SearchAuthorsAction` | `AuthorSearchService`, then `FoundAuthorsReader` |
| `search_groups` | `Groups\SearchGroupsAction` | `GroupSearchService`, then `FoundGroupsReader` |
| `find_tags` | `Catalogues\FindTagsAction` | `Tags\Dbal\TagSearchDbal` (see [search.md](search.md#typed-searches)) |
| `list_categories` | `Catalogues\ListCategoriesAction` | `CategoryManageService::getTree()`: id, parent, level, title, productions of the subtree |
| `list_hardware` | `Catalogues\ListHardwareAction` | `HardwareCatalogService`: codes by group, named in the current language |
| `list_options` | `Catalogues\ListOptionsAction` | `LegalStatus`, `ReleaseTypes`, `ReleaseFormatsProvider`, `LanguageCodes`, `GroupTypes`, `AuthorshipRoles`, `GroupMemberRoles` |
| `load_skill` | `Skills\LoadSkillAction` | `Assistant\Skills\SkillCatalogue` (see [Skills](#skills)) |
| `fetch_url` | `Web\FetchUrlAction` | `Shared\Http\PageFetcher` |
| `view_image` | `Web\ViewImageAction` | `Shared\Images\RemoteImageReader` (`url`), `StoredImageReader` (`fileId`) |
| `list_itch_io_files` | `Web\ListItchIoFilesAction` | `Releases\ItchIo\ItchIoDownloads::open()` (see [prod.md](prod.md)) |

The catalogue lists take no arguments (`NoArgumentsDto`: no properties, none
allowed); `OpenAiLlmClient` sends such a schema's `properties` as an empty
object. Every `limit` is 20 when not given and kept between 1 and 100
(`PageArguments::limit()`).

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

`list_itch_io_files` takes the address of an itch.io game or of any of its
pages (`ItchIoGameUrl`; another address is `ToolArgumentsException`) and
answers `ItchIoFilesDto`: the game's address and its uploads (`ItchIoUpload`:
id, the creator's label, native build), also those behind a donation prompt.
itch.io pages have no direct download links, and a file's storage address
lives a minute in the session that asked it, so a file is stored with
`set_release_itch_io_file`, which asks for the address when it runs.

Both go through `Shared\Http\GuardedHttpClient`, which `GuardedDownloader`
uses too: `http`/`https` only, every address — the first and each redirect, at
most 5 — passes `UrlGuard` (`PublicAddressGuard`), the request is pinned to
the checked address, the bodies of redirects are dropped and the body of the
last response goes to a consumer that can stop the transfer at a limit. A
refused address or a failed transfer is `HttpRequestRefusedException`. The
last response's `Content-Disposition` comes back with it, and a download
answers the file name it gives (`Shared\Http\ContentDisposition`).

The four typed searches take the fields of their filter flat, beside `limit`
and `offset` (`PageArguments`), and build the service's own filter DTO from
them (see [search.md](search.md#typed-searches)). The production filter is
written once (`Prods\ProdFilterArguments`: its properties, its schema and the
DTO built from them): `search_prods` takes its fields, the release, author and
group searches nest it as `prods`. The dates of adding a production are not
offered. Closed lists — legal statuses, release types, roles, group types, how
an entity is linked to a production — are `enum`s in the schema; language,
hardware and format codes are strings from `list_options` and
`list_hardware`. They answer the total and one page of summaries, in the order the
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

### Actions

An action is a tool of the assistant's own, in `ZxArt\Assistant\Actions`:
`Action` — `name()`, `description()`, `schema()`: the JSON Schema of its
arguments as a string. `ReadAction::read(Arguments)` answers a result DTO;
`WriteAction` adds `kind()` (`ChangeKind`), `entityType()`,
`state(Arguments)` — the target as it is now, null for a create — and
`write(Arguments)`, which builds the use case's command and answers its
`{id}`.

Each action has an arguments DTO (`<Action>ArgumentsDto`; `IdArgumentsDto` for
`{id}`): its `SCHEMA` constant is the schema, written as JSON beside the
constructor, with only the fields the task needs and
`additionalProperties: false`; a field it does not require also takes `null`
(in its type and its `enum`), which reads as not given, so a model that fills
every field leaves it out with `null`. `from(Arguments)` builds the DTO field
by field.
The action returns that constant from `schema()` and reads its arguments
through the DTO.

`Arguments::parse(json, schema)` refuses what is not a JSON object, a field the
schema does not have (`Unknown fields: …`) and a required one missing; `id()`,
`requiredText()`, `number()`, `text()`, `flag()`, `ids()`, `texts()` read a
value by its type and refuse another type, naming the field; `oneOf()` and
`textsOf()` read a text, or texts, of a closed list and refuse any other;
`object()` reads a nested object as arguments of its own schema, naming its
fields by their path (`prods.title`). All are `ToolArgumentsException`s, told
to the model. `Arguments::schema()` decodes a schema for whoever needs it
as an array: the tool list offered to the model and `describe_write_tool`.

The author actions, in `Assistant\Authors`, answer and show the assistant's own
DTOs, built by `AuthorDtos` from the element:

- `AuthorDto` — `id`, `title` (the nickname), `realName`, `countryId`,
  `cityId`, `wikiLink`, `refs` (the country and city), `aliases` (`AliasDto`),
  `groups` (`MembershipDto`), `counters` (`AuthorDetailsService::getCounters()`);
- `AuthorAliasDto` — `id`, `title`, `authorId`, `startDate`, `endDate` (Unix
  timestamps, 0 unknown), `refs` (the author), `groups`, `counters`.

The group actions, in `Assistant\Groups`, the same way through `GroupDtos`:

- `GroupDto` — `id`, `title`, `abbreviation`, `type` (`GroupTypes`),
  `countryId`, `cityId`, `website`, `wikiLink`, `slogan`, `startDate`,
  `endDate`, `subGroupIds`, `refs` (the country, city and subgroups),
  `aliases`, `members` (`MembershipDto`), `parentGroups` (from
  `structure_links`, `StructureLinksDbal::parentIds()`), `counters`
  (`GroupDetailsService::getCounters()`);
- `GroupAliasDto` — `id`, `title`, `groupId`, `startDate`, `endDate`, `refs`
  (the group), `members`, `counters`.

The production actions, in `Assistant\Prods`, through `ProdDtos`:

- `ProdDto` — `id`, `title`, `altTitle`, `year`, `description`,
  `instructions`, `legalStatus` (`LegalStatus`), `externalLink`, `youtubeId`,
  `partyId`, `partyPlace`, `compo`, `languages`, `hardware`, `categoryIds`,
  `groupIds` (groups and group aliases), `publisherIds` (groups, authors and
  their aliases), `tags`, `compilationItemIds` (productions and releases),
  `seriesProdIds`, `refs` (what the ids name), `authors` (`CreditDto`),
  `releases` (`FoundReleaseDto`), `files` (`ListedFileDto`: kind, id, file
  name), `inSeries`, `inCompilations` (from `structure_links`).

The release actions, in `Assistant\Releases`, through `ReleaseDtos`:

- `ReleaseDto` — `id`, `title`, `year`, `version`, `description`,
  `releaseType` (`ReleaseTypes`), `releaseFormats`, `languages`, `hardware`,
  `publisherIds`, `prodId`, `refs` (the publishers and the production),
  `authors`, `files` (`ListedFileDto` of the release's file kinds),
  `fileName`, `fileSize` (null when the release has no file or it is not
  stored on this server), `prodHardware`, `prodLanguages` (of its
  production).

The deny flags and the HTML flags of the description and instructions are not
offered; a text written by the assistant keeps the flag stored.

The fields carry the names the write actions take, so a plan finds what each
argument replaces in the state by its name.

| Action | Arguments | State |
|---|---|---|
| `get_author`, `get_author_alias` | `id` | — |
| `create_author` | `title`, `realName`, `countryId`, `cityId`, `wikiLink` | none |
| `update_author` | `id` and the fields of `create_author` | the author |
| `delete_author`, `convert_author_to_group` | `id` | the author |
| `join_authors` | `mainId`, `joinedId`, `asAlias` | the joined author |
| `create_author_alias` | `authorId`, `title`, `startDate`, `endDate` | none |
| `update_author_alias` | `id` and the fields of `create_author_alias` | the alias |
| `delete_author_alias`, `convert_author_alias_to_author` | `id` | the alias |
| `join_author_aliases` | `mainId`, `joinedId` | the joined alias |
| `get_group`, `get_group_alias` | `id` | — |
| `create_group` | `title`, `abbreviation`, `type`, `countryId`, `cityId`, `website`, `wikiLink`, `slogan`, `startDate`, `endDate`, `subGroupIds` | none |
| `update_group` | `id` and the fields of `create_group` | the group |
| `delete_group`, `convert_group_to_author` | `id` | the group |
| `join_groups` | `mainId`, `joinedId`, `asAlias` | the joined group |
| `set_group_member` | `groupId`, `authorId`, `roles`, `startDate`, `endDate` | the group |
| `remove_group_member` | `groupId`, `authorId` | the group |
| `create_group_alias` | `groupId`, `title`, `startDate`, `endDate` | none |
| `update_group_alias` | `id` and the fields of `create_group_alias` | the alias |
| `delete_group_alias`, `convert_group_alias_to_group` | `id` | the alias |
| `join_group_aliases` | `mainId`, `joinedId` | the joined alias |
| `set_group_alias_member` | `aliasId`, `authorId`, `roles`, `startDate`, `endDate` | the alias |
| `remove_group_alias_member` | `aliasId`, `authorId` | the alias |
| `get_prod` | `id` | — |
| `create_prod` | `title`, `altTitle`, `year`, `description`, `instructions`, `legalStatus`, `externalLink`, `youtubeId`, `partyId`, `partyPlace`, `compo`, `languages`, `hardware`, `categoryIds`, `groupIds`, `publisherIds`, `tags`, `compilationItemIds`, `seriesProdIds` | none |
| `update_prod` | `id` and the fields of `create_prod` | the production |
| `delete_prod` | `id` | the production |
| `join_prods` | `mainId`, `joinedId`, `releasesOnly` | the joined production |
| `set_prod_author` (link) | `prodId`, `authorId`, `roles` | the production |
| `remove_prod_author` (unlink) | `prodId`, `authorId` | the production |
| `add_prod_files` (attachFile) | `prodId`, `kind` (`ProdFileKind`), `urls` | the production |
| `delete_prod_file` (deleteFile) | `prodId`, `fileId` | the production |
| `get_release` | `id` | — |
| `create_release` | `title`, `prodId`, `year`, `version`, `description`, `releaseType`, `releaseFormats`, `languages`, `hardware`, `publisherIds` | none |
| `update_release` | `id` and the fields of `create_release` | the release |
| `delete_release` | `id` | the release |
| `set_release_author` (link) | `releaseId`, `authorId`, `roles` | the release |
| `remove_release_author` (unlink) | `releaseId`, `authorId` | the release |
| `add_release_files` (attachFile) | `releaseId`, `kind` (`ReleaseFileKind`), `urls` | the release |
| `delete_release_file` (deleteFile) | `releaseId`, `fileId` | the release |
| `set_release_file` (attachFile) | `releaseId`, `url`, `fileName` | the release |
| `set_release_itch_io_file` (attachFile) | `releaseId`, `url` (the itch.io game), `uploadId` | the release |

`set_release_file` stores the file under `fileName`, else the name the
response's `Content-Disposition` gives, else the one in the address.
`set_release_itch_io_file` opens the game's downloads, refuses an upload the
game does not offer (`ToolArgumentsException`, naming the ones it does), asks
the upload's storage address and stores it through the same use case, under
the name the storage sends.

Adding files answers `AddedFilesDto` (`id`, `fileIds`); every other write
`{id}` — of the new entity for a create or a conversion.

`set_*_member` is a link and `remove_*_member` an unlink; `roles` replace the
member's roles, a list given for `subGroupIds` replaces the stored one.

A field not given is left as it is (`null` in the use case's fields DTO); an
empty text or 0 clears it. The author's other settings (display flags, deny
flags, music settings) are not offered.

Tests: `tests/Assistant/Actions/ArgumentsTest.php`,
`tests/Integration/Assistant/AuthorActionsTest.php`,
`tests/Integration/Assistant/GroupActionsTest.php`,
`tests/Integration/Assistant/ProdActionsTest.php`,
`tests/Integration/Assistant/ReleaseActionsTest.php` (schemas, kinds, states,
writes, refusals, over `WriteToolTestCase`); the file actions in
`FileWriteToolsTest`; `tests/Integration/Entities/EntityDtosTest.php` (an id of
nothing).

Tests: `tests/Assistant/<Area>/` (the read actions with their services
mocked: schemas, the filters built from the arguments, limits, refusals),
`tests/Assistant/Actions/` (`Arguments`, the registry),
`tests/Integration/Assistant/ToolCallTest.php` (calls resolved from the
container and parsed from JSON, a nested filter among them, and every schema
closed at every depth), `tests/Integration/Entities/FoundEntitiesTest.php`
(the summaries);
`tests/Shared/Http/` and
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
`dependsOn` (the actions it takes ids from), `status` (`PlanActionStatus`),
`result`, `error`, `kind`, `entityType` and `state` (the normalized state DTO,
or null for a create and for an action that takes an id from an earlier one).

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
2. The model is offered every **read** tool, with its request's JSON schema
   (a read action with its own),
   `describe_write_tool` and `propose_plan`. Each reply is stored first — prose as a `text`
   message, each call as a `toolCall` message with its result — then the
   results are fed back. A failed call (unknown tool, bad arguments, a write
   tool called directly, a refusal) is told to the model as `Error: …`, and the
   run goes on.
3. The run ends `done` on a reply without calls, `waitingApproval` once a plan
   is proposed, `stopped` when the user asked it to stop (checked before each
   step, told as a `stopped` message), `failed` after 30 steps, 5 minutes or a
   model failure, with an `error` message.

### Skills

A skill is the assistant's instructions for one kind of task, a Markdown file
in `project/assistant-skills/`: `<name>.md`, opened by a frontmatter of `name`
(the file's name) and `description` (when to load it); the rest is the
instructions. `Skills\SkillCatalogue` reads them by name; a file that breaks
this is a `LogicException`, and `SkillCatalogueTest` reads the project's
skills, so a broken one fails the tests. `ConversationHistory` appends the
list — `- name: description` — to the system prompt; the model loads a skill
with `load_skill` before the task, and the instructions stay in the
conversation as that call's result. An unknown name is an
`UnknownSkillException`, told to the model with the names there are.

### Plans

`Agent\PlanProposer` handles `propose_plan` (`title`, `actions` of `tool` and
`arguments`, no other field at either level). Its description lists the write tools by name only
(`Agent\WriteToolCatalogue`); the model reads what one does and its argument schema with
`describe_write_tool` before it writes the tool into a plan, so the descriptions and schemas of
some thirty write tools do not ride along with every request.
Each action must name a write tool; `{"$ref": N}` in place of an id takes the
id action N creates, and must point to an earlier action (`PlanReferences`).
An action without references has its arguments checked against its schema
and its state read; when the arguments do not fit or the state finds nothing
(an id of nothing), the plan is refused and the reason goes back to the model.
An action with references has neither, because what it refers to does not
exist yet. The privileges and rules of the use case are not checked before the
plan runs: the use case refuses in `handle()`.

`Agent\PlanExecutor` runs an approved plan: each action through its write tool,
references resolved from the earlier results' `id`, stopping at the first
failure, or before the next action once the run is asked to stop (the rest are
`skipped`, the plan `failed` or `stopped`); an action writes from its
arguments, the ids in place, parsed by its schema. Each outcome is saved on the
plan and told as a `planProgress` message. Unless it was stopped, a `chat` run
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
`[[type:id]]` mention, an action's target and what it created (its
`entityType`, the `id` of its state and of its result), the refs its state
holds, and the ids its arguments and its state name in fields `<name>Id(s)`. Such a name tells no type
(`groupIds` holds group aliases too, `publisherIds` authors as well), so
`Chat\Mentions` reads those ids through `EntityRefsReader` and mentions each
as what it is, leaving out an id of nothing. All are found by
`EntityMentionLookup`: productions, releases, authors, groups and their
aliases, categories, parties, countries and cities.

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
  it stopped at), a `zx-step` per action: its kind and entity type, its target
  by the state's title (a link once known, also for what the plan created; a
  create's by its `title` argument), each argument but `id` and those given as `null` beside the value
  the state held under that name, dates as days, an error, the step a skipped
  one waited for, and for a delete, join or conversion the state's non-zero
  counters, releases and aliases as consequences. An id taken from an earlier
  step shows as that step's target with its number, a link once the step has
  created it. An argument named `<name>Id(s)` names the entity of that id with
  its title and id, found among the refs by the id alone (ids are unique
  across the site), else `#id`; an image address (png, jpg, gif, webp) shows
  as a small image. Adding files shows the files of that list in the state
  before the addresses; deleting a file names it by its file name; setting the
  release file shows the stored file name before the address.
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
  created on the first message and opened only once that message is sent,
  together with its run — a fetch sent alongside the message would answer it
  without the run and stop the polling; messages merged by `seq`, plans and run
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
action rows in `zx-item-data`, controls as `zx-button`. Their SCSS keeps only
the drawer's contour and scrolling; the drawer's own tokens are in
`shared/theme/_zx-assistant.theme.scss`.

Tests: `tests/Integration/Assistant/Chat/` (storage, the service),
`tests/Integration/Assistant/Agent/` (the run with a scripted model, plans
proposed and executed with real actions), `tests/Assistant/Agent/`
(history), `tests/Assistant/Llm/` (the OpenAI request and reply over the
client's fake); the specs next to the panel's services.
