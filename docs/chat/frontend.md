# AI assistant — frontend plan

Overview: [README.md](README.md). Endpoint and message kinds: [backend.md](backend.md).

## Where it lives

- `features/assistant/` — the panel, its components and store.
- `shared/api/AssistantDataApiService` — the only place that knows the
  `/assistant-data/` URL ([per-entity endpoint rule](../php/rest-api.md)).
- Models in `features/assistant/models/`.
- All text through the i18n bundles (en, ru, es); the agent's own text comes as
  is.
- Built from the design system: `zx-panel`, `zx-stack`, `zx-button`,
  `zx-dialog`, `zx-spinner`, `zx-callout`, `zx-user` and the form controls. No
  bespoke primitives; a missing one goes into `shared/ui/` with its own
  design-system document.

## Entry point and visibility

- A header button opens the panel. It is rendered only when
  `RootPrivilegeService` reports `aiAssistant`; without it nothing of the
  assistant exists on the page and no request is made.
- The panel is a **right-side drawer above the page**, outside the router
  outlet (next to `zx-player-view` in `spa-root`), so it stays open while the
  user navigates — that is how entity links are checked without closing the
  chat. On phones it takes the full width.
- Open/closed state and the selected conversation are remembered per browser
  (`localStorage`, failures ignored).

## Panel layout

1. **Header**: conversation title, a "conversations" toggle, "new", close.
2. **Conversation list** (toggle): the user's conversations newest first, title
   and last activity, a running indicator, delete behind a confirm dialog.
3. **Message stream**, one block per message kind:
   - `text` — user messages plain; assistant messages rendered as **Markdown**
     (sanitised), with `[[type:id]]` mentions turned into links;
   - `question` — the question, plus its options as quick-reply buttons that
     send the option text;
   - `toolCall` — one compact line with the human summary ("Searched authors
     'Fikus' — 3 found") and a **debug** icon;
   - `plan` — see below;
   - `planProgress`, `error`, `stopped` — compact status lines.
4. **Run status bar** while a run is active: spinner, the latest step's summary,
   elapsed time, and **Stop**.
5. **Composer**: textarea, send (Enter sends, Shift+Enter is a new line),
   disabled while a run is active. The current page is shown under it as a
   small chip ("Context: Dizzy — prod") so the user knows what the agent will
   see.

## Page context

Every `send` carries the current page: `url`, and `entityType` + `entityId` when
the route is an entity page. A small `AssistantPageContextService` reads them
from the router (the entity pages' route params), not from the DOM. A page
without an entity sends only the URL.

## Plan message

A special block under the assistant's plan:

- title and summary;
- numbered actions, each with: an icon and label for the kind (create, update,
  delete, join, convert, link, unlink, attach file, delete file); the target
  entity as a link (or "created in step N"); the change rows as
  field / before / after, with entity values as links; consequences as a
  `zx-callout`; delete and join actions visually marked as destructive;
- one **Approve plan** button, behind a `ConfirmDialogService` dialog that
  repeats the number of actions and the destructive ones;
- during and after execution each action shows its status (pending, running,
  done, failed with the error, skipped); the button is gone;
- a superseded plan is shown dimmed and without the button.

To change a plan the user simply writes another message.

## Entity links

Mentions and plan values are rendered from the message's `refs`
(`{title, url}`), so no request is made per link. A link is an ordinary
`routerLink` to the SPA route; the drawer stays open. A mention without a ref
is plain text.

## Debug modal

The debug icon on a tool call, a plan action or an error opens a large
`zx-dialog` with the full JSON of the message's `payload` (arguments, result,
preview, error), pretty-printed, with a copy button.

## Polling

`AssistantStore` (one per open panel) holds the conversation and its last
`seq`:

- poll `get&afterSeq=` every **2 s while a run is active**, every **15 s when
  idle**, and **not at all while the panel is closed** or the tab is hidden;
- appended messages are merged by `seq`; the plan and run status are replaced
  from each response;
- after `send`, `approvePlan` or `stop` the store switches to fast polling at
  once;
- HTTP errors are shown in the panel, not swallowed; a `403` closes the panel
  and hides the button.

Lists of messages render with `trackBy` on `id`, so polling never re-creates
existing blocks (and never breaks a button the user is pressing).

## Checklist

- [x] `AssistantDataApiService` + models + spec
- [ ] `AssistantStore`: load, send, stop, approve, delete, adaptive polling, merge by `seq` (+ spec)
- [ ] `AssistantPageContextService` from router params (+ spec)
- [ ] Header button behind `aiAssistant`; drawer in `spa-root`
- [ ] Conversation list with delete confirm
- [ ] Message blocks: text with Markdown and entity links, question with quick replies, tool call line, status lines
- [ ] Plan block with action rows, approve with confirm, live action statuses
- [ ] Debug modal with JSON and copy
- [ ] i18n keys in en/ru/es
- [ ] Markdown rendering library choice (sanitised output) — add to `package.json`
- [ ] Build with `composer run build`; `docs/features/ai-assistant.md` frontend section
