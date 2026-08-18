# Server sessions

`ServerSessionManager` is the only gateway to `$_SESSION`. Never touch the
superglobal or call `session_start()` directly.

## A session exists only once something is written to it

`controllerApplication::startSession()` does not start anything — it names the
session, sets its lifetime and marks the manager as enabled. Crawlers are
excluded there via `CrawlerDetect`.

The real start is lazy and asymmetric:

- `set()` starts a session, creating one if the visitor has none. Writing is what
  brings a session into being.
- `get()`, `delete()` and `getAll()` only attach to a session that already exists
  (a session cookie was sent, or one was started earlier in the request).
  Otherwise they return `null` / do nothing.

So an anonymous visitor who stores nothing on the server costs no session file
and receives no session cookie. **Do not add an unconditional session read to a
code path that runs on every request** — it is free now, but it stops being free
the moment it is paired with a write.

## What is allowed in the session

Only per-user state that genuinely cannot live anywhere else:

- `CurrentUser::$storage` — `currentUserId` after an explicit user switch, plus
  `userPrivileges` and `userGroupsIdList`.
- Admin-panel scratch state written through `CurrentUser::setStorageAttribute()`
  (copy/paste buffer, log filters). Admin-only, and it goes away with the panel —
  see [the retirement rules](../cms.md#the-admin-panel-is-being-retired).

**Anonymous visitors never get privileges written to the session.** Both
`storePrivileges()` and the `userGroupsIdList` write are guarded by
`isAuthorized()`; anonymous privileges are computed once per request and kept in
memory on the `CurrentUser` instance. That is sufficient and must stay that way.

The session is not a cache. Schema and configuration caches belong in `Cache`
(Redis in production); interface preferences belong to the Angular frontend
(`UserPreferencesService`), not to the server.

## Authentication does not depend on the session alone

`readUserId()` resolves the user from `storage['currentUserId']` first, then from
the `loginremember_{sessionName}` cookie, then falls back to the anonymous user.
The remember cookie outlives the session by months, so `CurrentUser::logout()`
clears it — otherwise an expired session would silently log the visitor back in.

## Language and theme are not in the session

The interface language and the theme are frontend preferences. See
[language & auto-login](../features/language-auth.md). The `adminLanguages` cookie
written by `LanguagesManager::persistAdminLanguageCode()` is the single remaining
server-side language memory, and it exists only for the admin panel.
