# AI assistant for editors — implementation plans

A chat panel for a small group of trusted editors. An editor gives the assistant
a task — about the page they are looking at, or about a page on another site —
and the assistant researches the archive, asks when something is ambiguous, and
proposes a **plan**: an ordered list of changes, each shown as type, entity,
before and after. Nothing is written until the editor approves the whole plan.

These documents are **plans for building it**, one per topic, each with a
checklist. They describe the intended design, not existing code. Once a part is
built, its description moves into the regular documentation (`docs/domain/`,
`docs/features/`, `php.md`, `angular.md`) and the plan's checklist is ticked.

A minimal first version runs: chat with read tools, plans proposed and executed
after approval, the drawer in the SPA — see
[../features/ai-assistant.md](../features/ai-assistant.md#chat). It leaves out,
for now: `ask_user`, the debug modal (the exchange log takes its place), cron
recovery of runs, separate `ai_plan_action` rows, the state of actions that take earlier
results, and re-reading the states of a plan at approval.

| Document | Topic |
|---|---|
| [frontend.md](frontend.md) | The chat panel in the SPA: conversations, polling, plan message, entity links, debug modal |
| [backend.md](backend.md) | Storage, endpoint, the agent run loop, plan execution, cron, cleanup, privileges |
| [mcp-tools.md](mcp-tools.md) | The tool catalogue the agent calls: read tools, write tools, control tools |
| [service-changes.md](service-changes.md) | What existing services, repositories, Dbal classes and legacy code must gain for the tools |
| [innodb.md](innodb.md) | Converting the MyISAM tables to InnoDB, for transactions and rollback in tests |

## Decisions taken

- **Model**: OpenAI for now (the existing `openai_key`); the model name is
  configuration, `gpt-6-luna` to start with.
  The client sits behind an interface so the provider can be switched.
- **Tools are internal services**, not an MCP server yet. Every tool has a
  JSON-Schema input generated from its request DTO and a typed result, so a
  public MCP endpoint later is only transport + authentication on top of the
  same registry.
- **Everything runs on the backend**, as the current user and with that user's
  privileges. The browser never calls a tool.
- **No websockets.** The SPA polls while the panel is open.
- **Run limits**: one agent run lasts at most 10 minutes; a cron job every
  15 minutes resumes runs that died. Every step the run takes is written to the
  database first.
- **Writes only through a plan.** Read tools run without confirmation. A plan is
  one logical act; it is approved as a whole with one button behind a confirm
  dialog, or discussed further. No per-action toggles, no undo.
- **Plan execution** is sequential. An action may reference the result of an
  earlier one (the id of an author created in step 1). On the first failure the
  run stops, the plan shows what was done and what was not, and the agent
  proposes a plan for the rest.
- **Scope of the first version**: productions, releases, authors, author
  aliases, groups, group aliases. Hardware is chosen from the existing catalogue
  on productions and releases. No music, graphics, parties, press, tags
  management or import ids.
- **Deletion** is allowed, one entity per action.
- **External sources**: the editor pastes a link; the agent fetches it with a
  plain HTTP GET (no headless browser), reads it, can look at images, and turns
  what it found into a plan. Existing portal importers are not used. Files are
  downloaded by the site when an approved action attaches them — there is no
  staging area.
- **Access**: only members of a dedicated user group. Everyone else sees nothing
  and every endpoint refuses them.
- **Language**: the assistant answers in the language the request was written
  in. Localised fields are not filled by it.
- **Retention**: a conversation is deleted a week after its last message, by a
  daily cron job, together with its runs and plans. The actions log keeps the
  record of what was changed.
- **The Smarty admin panel is not touched**: it is to be removed, so its
  `receive` actions keep writing the old way until then.
- **Domain documentation** (`docs/domain/ai-assistant.md`) is written separately
  once the feature exists.

## Order of work

1. [service-changes.md](service-changes.md) — the write and search services the
   tools need, each usable and testable on its own.
2. [innodb.md](innodb.md) — InnoDB, so integration tests roll back and plans run
   in a transaction.
3. [mcp-tools.md](mcp-tools.md) — tool contracts and registry on top of them.
4. [backend.md](backend.md) — storage, run loop, plans, endpoint, cron.
5. [frontend.md](frontend.md) — the panel.

Each step follows the mandatory workflow in `AGENTS.md`: research and approval,
failing tests first, implementation, review, checks, commit on approval.
