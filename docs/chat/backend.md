# AI assistant — backend plan

Overview and decisions: [README.md](README.md). Tools: [mcp-tools.md](mcp-tools.md).

## Namespaces

All new code lives in `project/core/ZxArt/Assistant/`:

| Namespace | Holds |
|---|---|
| `Assistant\Conversations` | conversations and messages: Dbal classes, DTOs, `ConversationService` |
| `Assistant\Runs` | `AgentRunner` (the loop), run Dbal, run limits |
| `Assistant\Plans` | plans and plan actions, `PlanExecutor`, reference resolution |
| `Assistant\Llm` | `ToolCallingClient` interface and its OpenAI implementation |
| `Assistant\Tools` | the tool registry and every tool (see [mcp-tools.md](mcp-tools.md)) |
| `Assistant\Prompt` | system prompt, page-context rendering, history assembly |

Controllers: `ZxArt\Controllers\AssistantData` (`/assistant-data/`) for the SPA,
`ZxArt\Controllers\AssistantCron` (`/assistant-cron/`) for the cron job.

## Storage

New tables, all **InnoDB** (the content tables are MyISAM, see
[Transactions](#transactions)). One migration in `db/migrations/`.

| Table | Columns (main) |
|---|---|
| `ai_conversation` | `id`, `userId`, `title`, `createdAt`, `lastMessageAt` |
| `ai_message` | `id`, `conversationId`, `seq`, `role` (`user`/`assistant`/`tool`/`system`), `kind`, `text`, `payload` (JSON), `createdAt` |
| `ai_run` | `id`, `conversationId`, `type` (`chat`/`execute`), `status`, `stopRequested`, `startedAt`, `heartbeatAt`, `finishedAt`, `steps`, `tokensIn`, `tokensOut`, `error` |
| `ai_plan` | `id`, `conversationId`, `messageId`, `title`, `status`, `approvedAt`, `finishedAt` |
| `ai_plan_action` | `id`, `planId`, `position`, `tool`, `arguments` (JSON), `kind`, `entityType`, `state` (JSON), `status`, `result` (JSON), `error`, `executedAt` |

- `seq` grows per conversation and is what the poll asks for: "everything after
  `seq` N". Every row the run writes gets the next `seq`, so the panel never
  misses a step.
- Message `kind`: `text` (user or assistant prose), `question` (the agent asks,
  optionally with suggested answers), `toolCall` (one read tool call with its
  human-readable summary; full arguments and result in `payload`), `plan`
  (points at `ai_plan`), `planProgress` (execution of one action), `error`,
  `stopped`. Assistant text and plans also carry `refs` in `payload` —
  see [Entity references](#entity-references).
- Run `status`: `queued` → `running` → `done` / `waitingApproval` / `failed` /
  `stopped`. Plan `status`: `proposed` → `approved` → `executing` → `done` /
  `failed`; a proposed plan becomes `superseded` once the user sends another
  message instead of approving it.
- Deleting a conversation deletes its messages, runs, plans and actions
  (foreign keys with `ON DELETE CASCADE`).

## Endpoint `/assistant-data/`

One controller, the [REST rules](../php/rest-api.md) apply: typed request DTOs
through the serializer, ObjectMapper for responses, HTTP status codes, no
`responseStatus`. Every action first checks the `aiAssistant` privilege
(see [Access](#access)) and that the conversation belongs to the current user
(`404` otherwise — another user's conversation does not exist for them).

| Request | Does |
|---|---|
| `GET ?action=list` | the user's conversations, newest first: id, title, lastMessageAt, whether a run is active |
| `GET ?action=get&id=&afterSeq=` | messages after `afterSeq`, the active run's status, the open plan with its actions. `afterSeq=0` is the full conversation |
| `POST ?action=create` | new empty conversation |
| `POST ?action=send` | body: `conversationId`, `text`, `page` (`url`, `entityType`, `entityId`). Stores the message, supersedes an unapproved plan, queues a `chat` run, starts it |
| `POST ?action=approvePlan` | body: `planId`. Marks it approved, queues an `execute` run, starts it |
| `POST ?action=stop` | body: `conversationId`. Sets `stopRequested` on the active run |
| `POST ?action=delete` | body: `conversationId` |

`send` is refused with `409` while a run is active: one run per conversation.
The title of a conversation is the first 80 characters of its first message.

## Starting a run without blocking the request

The run must outlive the request that started it, and the request must answer
at once so the panel can start polling.

- Production (zone.ee) runs Apache with PHP-FPM: the request answers, then
  calls `fastcgi_finish_request()` and continues with the run.
- The local environment is `php:8.5-apache` (mod_php), where that function does
  not exist. There the request answers with `Content-Length` and
  `Connection: close`, flushes every buffer and continues under
  `ignore_user_abort(true)`. One small `ResponseCloser` service hides the
  difference (`function_exists('fastcgi_finish_request')`).
- Either way the cron job below picks up a `queued` run that nobody started.
- The session must be **closed before the run starts** (`session_write_close()`
  through `ServerSessionManager`, see [service-changes.md](service-changes.md)):
  PHP file sessions are locked per request, and an open one would block every
  poll of the same user for up to ten minutes.
- `set_time_limit()` covers the 10-minute limit plus a margin.

## The run loop (`AgentRunner`)

A run takes the conversation, claims the run row
(`UPDATE … SET status='running' WHERE id=? AND status='queued'` — a second
starter gets zero rows and leaves), and loops:

1. Check `stopRequested` and the deadline (10 minutes since `startedAt`).
   On either, write a `stopped` message, set the status, end.
2. Assemble the request: system prompt, conversation history, the page context
   of the latest user message, tool definitions (read tools + control tools).
3. Call the model. Store the assistant turn as rows **before** acting on it.
4. For every read tool call: run the tool as the current user, store a `toolCall`
   message (summary + full arguments/result in `payload`), feed the result back.
5. Control tools end the turn:
   - `ask_user` → `question` message, run `done`;
   - `propose_plan` → the plan is validated and each target's state read
     (see [Plans](#plans)); a valid one becomes a `plan` message and the run
     ends `waitingApproval`; an invalid one is returned to the model as a tool
     error so it can correct it;
   - plain text with no tool call → `text` message, run `done`.
6. Update `heartbeatAt`, `steps`, token counters after every step.

Limits, all configuration: 10 minutes per run, a maximum number of model calls
per run, a maximum size of one tool result fed back (larger results are
truncated with a note saying so, the full result stays in `payload`).

Errors: a tool that throws is reported back to the model as a tool error
(the model may retry or ask the user); a model call that fails is retried once,
then the run ends `failed` with an `error` message.

### History and context

- Every message the user sends carries the page they are on. The prompt
  presents it as "The user is looking at: prod 123 (/prod/123)". The backend
  does not load the page — the agent calls `get_prod` if it needs it.
- History is replayed from `ai_message`. Tool results of earlier turns are
  replayed in truncated form; the current turn gets them in full.
- The system prompt states: answer in the language of the user's message; be
  certain before proposing — ask on any ambiguity, including several matching
  entities in the archive; writes only through `propose_plan`; one plan is one
  logical act; reference entities as `[[type:id]]`; do not fill localised fields.

## Running as the user

Every tool runs as the user who owns the conversation, with privilege checking
**on** and the **public** structure manager — the same context the SPA's own
requests have. This is the opposite of `Crontab`, which runs as the `crontab`
user with privileges off on the admin structure manager, and is the reason the
assistant has its own cron controller instead of a step in `Crontab`.

- A run started from `/assistant-data/` already has the user's session.
- A run resumed from cron runs as the `crontab` user for now, with privilege
  checking on and the public structure manager. `crontab` holds wide
  privileges, so a resumed run may do what its owner could not; it is accepted
  because only trusted editors use the assistant and every write still needs
  the owner's approval of the plan in the panel. Changes made by a resumed run
  are logged under `crontab`. Running as the owner (`switchUser()` plus
  privileges compiled for them) is a later improvement.
- A user removed from the assistant group loses access at once: the runner
  checks `aiAssistant` again when it resumes and when a plan starts executing.

## Plans

`propose_plan` carries a title and an ordered list of actions, each a write tool
name with its arguments.

Validation before the plan is shown:

1. Every tool exists and is a write tool; arguments deserialize into its request
   DTO.
2. **References** to earlier actions are written as
   `{"$ref": "<position>.<field>"}` — for example the `id` of the author created
   in action 1. A reference must point at an earlier action and at a field its
   result type declares.
3. Each action without references reads its target's current state
   (`WriteAction::state()`), stored with the action for the panel. A missing
   entity makes the plan invalid; privileges and the use case's rules are
   checked only when the action runs, and a refusal there is a failed step the
   assistant then plans around. An action whose input is a reference has no
   state: what it refers to does not exist yet.

### Execution (`PlanExecutor`)

An `execute` run walks the actions in order:

1. Resolve references from the results of the actions already done.
2. Run the action's `write()` as the user: the use case checks the privilege
   and writes the actions log, as every entity write does.
3. Store the result and status and write a `planProgress` message.

On the first failure the remaining actions are marked `skipped`, the plan
`failed`, and a `chat` run is queued with a system note listing what was done
and what was not, so the agent can explain and propose a plan for the rest.
After success the plan is `done` and the agent gets a short note to confirm it.

A stop request during execution finishes the current action and then stops;
the rest are `skipped`.

### Transactions

Wrapping a plan in one database transaction is **not possible today**, and
would not be enough even if it were:

- All content tables — `structure_elements`, `structure_links`,
  `module_zxprod`, `module_zxrelease`, `module_author`, `module_group`,
  `module_file`, `import_origin`, `actions_log` and most others — are
  **MyISAM**, which ignores transactions. Only a few newer tables
  (`authorship`, the release hardware and language tables) are InnoDB, so a
  rollback would undo half a change.
- Converting them is a separate project: a long `ALTER TABLE … ENGINE=InnoDB`
  per table with locking on production, a check of every `FULLTEXT` index, and
  regression testing of the whole site.
- Even on InnoDB, a rollback leaves behind files downloaded to disk, the element
  cache, the structure manager's in-memory elements and queued AI jobs.

The conversion is planned in [innodb.md](innodb.md). Until it is done, plan
execution relies on the **state pass** before anything is written (it catches
missing entities and invalid input) and on **stop on
first failure** with a clear report. Once the tables are InnoDB, the executor
wraps the actions in `$db->transaction()`; files written to disk during a failed
plan are deleted by the executor, and caches of the touched elements are
dropped.

## Entity references

The agent writes entity mentions as `[[prod:123]]`, `[[release:5]]`,
`[[author:7]]`, `[[authorAlias:8]]`, `[[group:9]]`, `[[groupAlias:10]]`.
When a message is stored, the backend collects them, loads each entity and
stores `refs: {"prod:123": {"title": "…", "url": "/prod/123"}}` in the
message's `payload` (URLs from `EntityUrlResolver`). The panel renders links
from `refs` without extra requests. A mention of something that does not exist
stays plain text. Plans carry refs the same way.

## Cron `/assistant-cron/`

Run every 15 minutes by the server's crontab, like the existing cron URLs.

- Resume: every run `queued` for more than a minute, or `running` with
  `heartbeatAt` older than 2 minutes (the process died), is restarted — the
  loop continues from the stored messages; an `execute` run continues with the
  first action that is not `done`. An action that was `running` when the process
  died is marked `failed` ("interrupted") rather than repeated, because it may
  have half-written.
- Cleanup, once a day: conversations with `lastMessageAt` older than 7 days are
  deleted.

## Access

- New root privilege `aiAssistant`, checked on the public root like
  `editHardware` ([manage-section.md](../features/manage-section.md)).
- New user group `ai-assistant-users` holding it, created **empty** by the
  migration; membership is decided in the admin panel.
- Without the privilege `/assistant-data/` answers `403` for every action and
  the SPA shows nothing.
- Read tools see only what the user may see; write tools check the privilege of
  the matching entity action for the user.

## Configuration

`main` config: `ai_assistant_model` (`gpt-6-luna`), `ai_assistant_run_minutes` (10),
`ai_assistant_max_steps`, `ai_assistant_retention_days` (7),
`ai_assistant_fetch_max_bytes`. The API key is the existing `ai_key`.

## Checklist

- [ ] Migration: five InnoDB tables, `aiAssistant` privilege, empty `ai-assistant-users` group
- [ ] Dbal classes for conversations, messages, runs, plans, actions (+ SQLite Dbal tests)
- [ ] `ToolCallingClient` interface + OpenAI implementation (tools, image input, token usage)
- [ ] System prompt and history assembly with truncation
- [ ] `AgentRunner`: claim, loop, stop, deadline, heartbeat, step and size limits
- [ ] Control tools `ask_user`, `propose_plan`; plan validation, references, target states
- [ ] `PlanExecutor`: references, stop on failure, follow-up run
- [ ] Entity reference extraction into `refs`
- [ ] `/assistant-data/` controller with request DTOs, REST DTOs, privilege and ownership checks
- [ ] `ResponseCloser` (FPM / mod_php) + session close before the run
- [ ] `/assistant-cron/`: resume dead runs as `crontab`, daily cleanup; crontab entry
- [ ] OpenAPI spec in `api/` for `/assistant-data/`
- [ ] `docs/features/ai-assistant.md`, `docs/domain/ai-assistant.md`, `domain.md` entry
