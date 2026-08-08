# Token authentication for the public site — implementation plan

Goal: the public SPA authenticates with a short-lived **access token** sent as
`Authorization: Bearer`, backed by a long-lived **refresh token** delivered in an
httpOnly cookie scoped to the refresh endpoint. The public backend becomes
sessionless. The `loginremember_*` cookie mechanism is deleted.

This document is the working checklist. Every box is a discrete, reviewable step.
All facts below were verified against the code; file:line references are exact at
the time of writing.

---

## 0. Where the current system stands (verified)

| Fact | Location |
| --- | --- |
| Identity is resolved in exactly one method | `trickster-cms/cms/core/App/Users/CurrentUser.php:324` `readUserId()` |
| Almost everything reads the user through one service | `App\Users\CurrentUserService::getCurrentUser()` |
| **Exception:** `EventsLog` injects `CurrentUser` directly through PHP-DI | `trickster-cms/cms/core/App/Logging/EventsLog.php:28` — violates the rule in `docs/cms.md` |
| Remember cookie is derived from the password hash, cannot be revoked individually | `CurrentUser.php:207-229`, `readUserId()` |
| **71** of the 81 files in `project/core/ZxArt/Controllers/` call `startSession('public')` | 3 use `'crontab'`, 7 use none (`Crontab`, the base class, `CrontabException`, `Rss`, `Socialpost`, `Zximages`, `Zximagesdownload`) |
| **79** controller subclasses call `parent::__construct($controller, $logger)` with exactly two arguments | any change to the base constructor signature is a 79-file change |
| Session holds `currentUserId`, `userPrivileges`, `userGroupsIdList` … | `docs/cms/sessions.md` |
| … plus `last_captcha` (dead code) and `lastSearchPhrase`/`lastSearchId` (call site commented out) | `homepage/modules/applications/captcha.class.php:27`, `cms/core/searchQueriesManager.class.php:64-72` |
| `session_write_close()` is never called → the session lock is held for the whole request | `ServerSessionManager::close()` has no callers |
| Anonymous visitors never get a session | `ServerSessionManager::sessionExists()` |
| Public site is 100% SPA | only `project/templates/public/index.spa.tpl` remains |
| `/ajax/` and `/ajaxSearch/` reflect any `Origin` with `Access-Control-Allow-Credentials: true` | `homepage/modules/applications/ajax.class.php:16-27`, `cms/modules/applications/ajaxSearch.class.php:26-38` |
| Crontab/import apps authenticate by `checkUser('crontab', null, true)` + `switchUser()` | `Crontab.php:154`, `Pouet.php:55`, `Sam.php:53`, `Zxdb.php:55`, `VisitorRemove.class.php:26` |
| `Crontab` never starts a session and does not extend `LoggedControllerApplication` | `Crontab.php:42` |
| Admin panel uses a separate session name (`admin`) | `adminAjax.class.php:10` |
| `project/` **shadows** `trickster-cms/cms/` for module files — the admin login form runs `project/modules/structureElements/login/` | `PathsManager::addIncludePath()` unshifts into `reversedIncludePaths` (`:60-64`), read by `getIncludeFilePath()` (`:45-53`) |
| `Cache` is **disabled by default** and only enabled as a side effect of `IpBanService::__construct` | `Cache.php:13`, `Cache.php:28`, `IpBan/IpBanService.php:23` |
| The public structure manager checks privileges by default | `structureManager.php:51` `$privilegeChecking = true` |
| Three frontend services build protocol-relative `//host/ajax/` URLs | `vote.service.ts:11`, `tags-search.service.ts:13`, `playlist.service.ts:16` |
| `htdocs/.htaccess` does **not** forward the `Authorization` header, and the vhost has no `CGIPassAuth` | `htdocs/.htaccess:26`, `docker/php/vhost.conf` |
| Config secrets live in the gitignored `project/config/live/` | `.gitignore:8` |

Consequences that shape the design:

1. Identity has a single *service* gateway, so the token can be resolved inside
   `CurrentUserService` — no controller needs to change (§4.1). The one class that
   bypasses the gateway, `EventsLog`, must be fixed.
2. `switchUser()` mutates an in-memory array before re-reading it, so crontab and
   import flows keep working with no session and no token — provided an explicit
   switch keeps priority over the token resolver.
3. Privileges are cached in the session today. Removing the session without a
   replacement cache is a regression, and the replacement cache cannot rely on
   `Cache` being enabled.

---

## 1. Design decisions (locked)

### 1.1 Access token

- Format: `base64url(payloadJson) . '.' . base64url(hmacSha256(payload, secret))`.
  Deliberately not a full JWT library — the project already carries this exact
  pattern in `ZxArt\Users\PasswordResetTokenService`.
- Payload: `{"uid":int,"sid":string,"iat":int,"exp":int}` — user id, refresh
  family id, issued-at, expiry.
- TTL: **900 s (15 min)**. Clock skew tolerance on verification: 30 s.
- Transport: `Authorization: Bearer <token>` only. Never a cookie, never a query
  parameter.
- Contains **no privileges and no user name** — privileges change under the
  user's feet, so they must never be frozen into a bearer token.

### 1.2 Refresh token

- Format: `<rowId>.<hex(random_bytes(32))>`. The row id makes lookup a
  primary-key hit instead of a scan over hashes.
- Storage: only `hash('sha256', $secret)` is persisted.
- Transport: cookie
  - name `zxrt`
  - `HttpOnly`
  - `Secure` — derived from the **actual request** (`controller.class.php:170-188`
    already detects HTTPS), not from `main.protocol`, which is `'http://'` in the
    committed config and would silently drop the flag on a live https deployment
  - `SameSite=Strict` — the refresh call is always a same-site XHR from an
    already-loaded page
  - `Path=/auth-data/` — no other endpoint receives ambient authority.
    **Every client call must use the trailing slash**: RFC 6265 path-matching
    would not send the cookie to `/auth-data?action=refresh`
  - `Max-Age` = 90 days when "remember me" is checked; **omitted** (session
    cookie) when it is not. That is the entire implementation of "remember me" —
    no second mechanism, no `localStorage`, no `sessionStorage`.

### 1.3 Rotation, reuse detection and the multi-tab race

Every successful refresh rotates the token. A replayed token means either a
racing second tab or a theft, and the two must be told apart without weakening
detection.

Three mechanisms, in order of who handles what:

1. **Client-side single flight (primary).** All tabs share one cookie, so only one
   tab should ever refresh. `navigator.locks.request('zx-auth-refresh', …)`
   serialises them across tabs; the winner broadcasts the new access token. This
   makes a server-side replay a rare event rather than the normal case.
2. **Server-side replay cache (safety net).** On successful rotation, store
   `replay:{sha256(presented)} -> {newPlaintext, expiresAt}` for **60 s**.
   A replay inside that window returns the *same* successor token. This preserves
   exactly one live token per family, which is what makes step 3 meaningful.
   Requires `Cache`; `RefreshTokenService` enables it itself (§4.5). If the cache
   is unavailable the window degrades to zero and step 3 applies — noisy, not
   unsafe.
3. **Reuse detection.** A rotated or revoked token presented outside the replay
   window revokes the **entire family** and returns 401.

> Rejected alternative: "issue a fresh sibling in the same family" during the
> grace window. It forks the family so both the thief and the legitimate user
> keep rotating their own branch, and reuse detection never fires again. Do not
> reintroduce it.

**Concurrency:** `rotate()` runs inside a transaction and selects the token row
`FOR UPDATE`. Without it two simultaneous refreshes both observe
`rotated_at IS NULL`, both insert successors, and the second `markRotated()`
overwrites the first `successor_id`.

### 1.4 Sessions

The public site stops using PHP sessions entirely. Admin keeps its own session
untouched. Crontab apps keep the `crontab` session name (harmless, and their
`switchUser()` does not depend on it).

### 1.5 Privileges

Moved from session storage to `Cache`, keyed by user id, with invalidation on
every privilege **and every group-membership** write path (§4.6).

### 1.6 Login throttling

Backed by a **database table**, not by `Cache`. `Cache` is disabled by default
and only enabled by an incidental side effect (`IpBanService`), and a silently
unthrottled login endpoint is a security regression rather than a performance
one. Login is low-frequency; one indexed upsert per attempt is affordable.

### 1.7 What gets deleted

The `loginremember_*` cookie is removed for **both** public and admin. It is a
password-hash-derived, individually non-revocable, 90-day credential — exactly
the mechanism this migration exists to replace. Admins log in through the admin
form when their session expires; admin session lifetime is already 86400 s.

---

## 2. Database changes

### 2.1 Migration file

`db/migrations/2026.08.08 - auth-tokens.sql`

Conventions taken from the two most recent migrations
(`2026.02.04 - user-preferences.sql`, `2025.09.08 - ipban.sql`): `snake_case`
columns, `COLLATE=utf8mb4_bin`, explicit `engine_` prefix, foreign keys are
allowed (the user-preferences migration uses one).

```sql
CREATE TABLE `engine_user_refresh_tokens` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED NOT NULL,
    `family_id`         CHAR(32)     NOT NULL,
    `token_hash`        CHAR(64)     NOT NULL,
    `created_at`        INT UNSIGNED NOT NULL,
    `expires_at`        INT UNSIGNED NOT NULL,
    `last_used_at`      INT UNSIGNED          DEFAULT NULL,
    `rotated_at`        INT UNSIGNED          DEFAULT NULL,
    `successor_id`      INT UNSIGNED          DEFAULT NULL,
    `revoked_at`        INT UNSIGNED          DEFAULT NULL,
    `persistent`        TINYINT(1)   NOT NULL DEFAULT 0,
    `ip`                VARBINARY(16)         DEFAULT NULL,
    `user_agent`        VARCHAR(255)          DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_user` (`user_id`),
    KEY `idx_family` (`family_id`),
    KEY `idx_expires` (`expires_at`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;

CREATE TABLE `engine_auth_login_attempts` (
    `id`                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `scope`             VARCHAR(8)    NOT NULL,
    `subject`           VARBINARY(190) NOT NULL,
    `failures`          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `window_started_at` INT UNSIGNED  NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_scope_subject` (`scope`, `subject`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_bin;
```

- `scope` is `'ip'` or `'user'`.
- Repositories must **not** write the `engine_` prefix — Illuminate adds it
  (`docs/php/repositories.md:14`).
- Add both tables to `ZxArt\Shared\DatabaseTable` (`docs/php/repositories.md:15`
  forbids local table-name constants).

### 2.2 Cleanup

In the `$minutes >= 43 && $minutes <= 46` branch of
`ZxArt\Controllers\Crontab::execute()`, next to `cacheCleanupService->cleanup()`:

- delete refresh tokens with `expires_at < time() - 86400`
- delete login-attempt rows with `window_started_at < time() - 86400`

### 2.3 No other schema change

Users, groups and privileges are untouched.

---

## 3. Backend: new code

All new classes go under `project/core/ZxArt/Auth/`.

### 3.1 `AccessTokenService.php`

```
readonly class AccessTokenService
    issue(int $userId, string $familyId): AccessToken      // {token, expiresIn}
    verify(string $token): ?AccessTokenClaims              // null on any failure
```

- Secret from `ConfigManager` key `main.authTokenSecret`.
- **Throws on an empty secret** rather than signing with `''`.
- `hash_equals` for comparison. No DB access. Fully unit-testable.

### 3.2 `Dto/AccessTokenClaims.php`

`readonly class AccessTokenClaims { int $userId; string $familyId; int $expiresAt; }`

### 3.3 `Repositories/RefreshTokenRepository.php`

`final readonly class`, **extends `ZxArt\Shared\Repositories\AbstractRepository`**
(`docs/php/repositories.md:8`), table name from `DatabaseTable`.

```
findByIdForUpdate(int $id): ?array        // inside a transaction
insert(int $userId, string $familyId, string $tokenHash, int $expiresAt,
       bool $persistent, ?string $ip, ?string $userAgent): int
markRotated(int $id, int $successorId, int $rotatedAt): void
touch(int $id, int $lastUsedAt): void
revokeById(int $id, int $revokedAt): void
revokeFamily(string $familyId, int $revokedAt): void
revokeAllForUser(int $userId, int $revokedAt): void
listActiveForUser(int $userId, int $now): array
deleteExpired(int $threshold): int
```

Query Builder only, no raw SQL (`docs/php/repositories.md:21`).

### 3.4 `Repositories/LoginAttemptRepository.php`

Same conventions. `registerFailure(string $scope, string $subject, int $now, int $windowSeconds): int`,
`countFailures(...)`, `reset(string $scope, string $subject)`, `deleteStale(int $threshold)`.

### 3.5 `RefreshTokenService.php`

```
issue(int $userId, bool $persistent): IssuedRefreshToken       // new family
rotate(string $presented): RotationResult
revoke(string $presented): void
revokeAllForUser(int $userId): void
```

`rotate()` — inside `$db->transaction()`:

1. Split `<id>.<secret>`; malformed → invalid.
2. Check the replay cache for `sha256(presented)`; a hit returns the stored
   successor plaintext and stops here.
3. `findByIdForUpdate($id)`; missing → invalid.
4. `hash_equals(row.token_hash, sha256($secret))`; mismatch → invalid.
5. `revoked_at !== null` → **revoke family**, invalid.
6. `expires_at <= now` → invalid.
7. `rotated_at !== null` (and no replay-cache hit at step 2) → **revoke family**,
   invalid.
8. Insert successor in the same family, `markRotated($id, $successorId, $now)`,
   write the replay-cache entry (TTL 60 s), return the new plaintext.

The successor inherits the family's absolute expiry — refreshing does not extend
a 90-day session indefinitely.

### 3.6 `AuthenticationService.php`

Depends on `LoginService`, `AccessTokenService`, `RefreshTokenService`,
`LoginThrottle`, `CurrentUserService`, `CurrentUserRestService`.

```
login(string $userName, string $password, bool $remember): LoginResult
refresh(?string $presentedRefresh): RefreshResult
logout(?string $presentedRefresh): void
logoutEverywhere(int $userId): void
```

**Both `login()` and `refresh()` must call
`CurrentUserService::setResolvedUserId($userId)` before building the response
DTO.** `CurrentUserRestService::buildDto()` reads the current user
(`CurrentUserRestService.php:18`); on a refresh request there is no bearer token,
so without this the endpoint would return a valid access token alongside an
`anonymous` user object.

### 3.7 `RefreshCookie.php`

The single owner of the cookie contract: name, path, flags, lifetime, `write()`,
`clear()`, `read()`. Nothing else in the codebase may `setcookie()` for it.
`Secure` comes from the controller's HTTPS detection, not from config.

### 3.8 `BearerTokenReader.php`

Reads `Authorization: Bearer …` from `$_SERVER`, handling `HTTP_AUTHORIZATION`
and `REDIRECT_HTTP_AUTHORIZATION`. Returns `?AccessTokenClaims`.

**This class alone is not enough.** See §4.2 — the header must be forwarded by
Apache first.

### 3.9 `LoginThrottle.php`

DB-backed (§1.6). Window 15 min, 10 failures per IP and 10 per user name → 429
with `Retry-After`. A successful login resets the user-scoped counter.

### 3.10 `App\Users\AuthenticatedUserIdResolver` (interface, in trickster-cms)

```php
interface AuthenticatedUserIdResolver { public function resolve(): ?int; }
```

Lives in the CMS so `CurrentUserService` can depend on it without the engine
reaching into the `ZxArt` namespace. A null-object default is bound in the CMS;
`project/core/di-definitions.php` binds it to the real
`ZxArt\Auth\BearerUserIdResolver` (which wraps `BearerTokenReader`).

---

## 4. Backend: changes to existing code

### 4.1 Identity resolution — no controller changes at all

This replaces the naive "wire it into the base controller" approach. Adding
constructor parameters to `LoggedControllerApplication` would break **79**
subclasses that call `parent::__construct($controller, $logger)`.

**`trickster-cms/cms/core/App/Users/CurrentUserService.php`**

- Inject `AuthenticatedUserIdResolver`.
- Add `setResolvedUserId(?int $userId): void` for explicit callers (login,
  refresh, tests).
- In `getCurrentUser()`, before `initialize()`:
  `$user->setResolvedUserId($this->resolvedUserId ?? $this->resolver->resolve());`

Because `CurrentUserService` is the gateway for every consumer — including
`/ajax/`, `/ajaxSearch/`, `structureElementAction`, `votesManager`,
`tagsManager`, `privilegesManager` — a single change authenticates the whole
public surface. Verified: no service resolves `getCurrentUser()` inside a
constructor, so resolution is never too early.

Admin is unaffected: admin requests send no bearer header, the resolver returns
`null`, and session storage keeps priority anyway.

**`trickster-cms/cms/core/App/Users/CurrentUser.php`**

- Add `private ?int $resolvedUserId` + `setResolvedUserId()`.
- `readUserId()` new order:
  1. `$this->storage['currentUserId']` — **stays first** (this is what
     `switchUser()` writes, and it is how crontab/import/admin work)
  2. `$this->resolvedUserId`
  3. anonymous
- **Delete** the `loginremember_` branch, `rememberUser()`, `forgetUser()`, and
  the `forgetUser()` call in `logout()`.
- `loadPrivileges()` / `getGroupsIdList()` / `refreshPrivileges()` switch from
  `$this->storage` to `PrivilegeCache` (§4.6).
- Do **not** add a `PrivilegeCache::invalidate()` call to `switchUser()`. Once
  the session is gone its `unset($this->storage[...])` is already a no-op for
  cached state, and the id being switched away from is normally `anonymous`,
  which is never cached.

**`trickster-cms/cms/core/App/Logging/EventsLog.php`**

- Replace the injected `CurrentUser $user` (`:28`) with `CurrentUserService`.
  This is the only class that bypasses the gateway, and `docs/cms.md` already
  forbids what it does.
- `$object->session` (`:144`) currently stores `ServerSessionManager::getSessionId()`,
  which becomes permanently empty. Store the access token's `familyId` instead,
  or drop the column from the write — decide when touching it, but do not leave
  it silently writing `''`.

### 4.2 Apache must forward the `Authorization` header

`htdocs/.htaccess` rewrites everything to `index.php` but never exports the
header, and `docker/php/vhost.conf` sets no `CGIPassAuth`. Under mod_php this
usually works; under FPM/CGI it does not, and the failure mode is "everyone is
anonymous", which is easy to misdiagnose.

- [ ] Add to `htdocs/.htaccess`, before the catch-all rewrite:
      `RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]`
- [ ] Add `CGIPassAuth On` to the `<Directory>` block in `docker/php/vhost.conf`.
- [ ] Verify on the real production vhost, not only in Docker.

### 4.3 `project/core/ZxArt/Controllers/AuthData.php` (new)

URL `/auth-data/` — verified: `controller::detectApplication()` (`controller.class.php:324-338`)
runs `toPascalCase('auth-data')` → `AuthData`. `Auth` would claim `/auth`, which
the SPA may want for a login page later.

`extends LoggedControllerApplication`, `$rendererName = 'json'`, **no session**.
Actions: `login`, `refresh`, `logout`, `logout-all`, `sessions`.

### 4.4 `project/core/ZxArt/Controllers/Currentuser.php`

- `handleLogin()` / `handleLogout()` removed — they move to `AuthData`.
- `GET /currentuser/` keeps its URL and response shape, no session start.

### 4.5 CORS holes — delete

- `trickster-cms/homepage/modules/applications/ajax.class.php:16-27`
- `trickster-cms/cms/modules/applications/ajaxSearch.class.php:26-38`

Both reflect an arbitrary `Origin` with `Access-Control-Allow-Credentials: true`,
which today lets any website perform authenticated form saves, deletes and votes
on behalf of a logged-in user and read the response. Bearer tokens remove the
ambient authority; the headers must go too, or the same class of bug returns.

### 4.6 Privilege cache — `project/core/ZxArt/Auth/PrivilegeCache.php` (new)

```
getPrivileges(int $userId): ?array / setPrivileges(int $userId, array $p): void
getGroups(int $userId): ?array     / setGroups(int $userId, array $g): void
invalidate(int $userId): void
```

- Backed by `Cache`, keys `userPrivileges:{id}` / `userGroups:{id}`, TTL 3600.
- **Calls `$cache->enable(true, true, true)` itself.** `Cache::$enabled` defaults
  to `false` and is currently only switched on as a side effect of
  `IpBanService::__construct`. Relying on that is not acceptable. If
  `cache.enabled` is false in config, the cache degrades to a no-op and
  privileges are recomputed per request — a perf hit, not a correctness one.
- Anonymous is still never cached.

Invalidation must be hooked into **every** write path:

| Path | Location |
| --- | --- |
| `setPrivilege()` | `privilegesManager.class.php:37` |
| `deletePrivilege()` | `privilegesManager.class.php:69` |
| Registration group assignment | `ZxArt/Registration/RegistrationService.php:91` |
| Admin editing a user's groups | `project/modules/structureElements/user/action.receive.class.php:37,54` |
| Legacy registration submit | `project/modules/structureElements/registration/action.submit.class.php:126` |
| Deployment / fixtures | `trickster-cms/cms/core/DeploymentManager.php:1584` |

`copyPrivileges()` needs no hook — it funnels through `setPrivilege()`.
Separately, `ZxArt\Playlists\PlaylistService.php:82-83` sets privileges without a
following `refreshPrivileges()`; fix that while here.

### 4.7 Remove `startSession(...)` from the public side

Verified full list — the earlier draft missed a third of it.

**`project/core/ZxArt/Controllers/` — 71 files** with `startSession('public')`.

**Other applications:**
- `project/modules/applications/file.class.php:17`
- `project/modules/applications/releasefile.class.php:18`
- `project/modules/applications/zxfile.class.php:17`
- `project/modules/applications/screenshot.class.php:17`
- `project/modules/applications/route.class.php:15`
- `project/modules/applications/zipItems.class.php:26` (`$mode = 'public'`)
- `trickster-cms/cms/modules/applications/file.class.php`
- `trickster-cms/cms/modules/applications/remote.class.php`
- `trickster-cms/cms/modules/applications/jsonElementData.class.php:15`
- `trickster-cms/cms/modules/applications/api.class.php` (public mode only)
- `trickster-cms/homepage/modules/applications/public.class.php:29` (the SPA shell)
- `trickster-cms/homepage/modules/applications/captcha.class.php:17`
- `trickster-cms/homepage/modules/applications/redirect.class.php`
- `trickster-cms/homepage/modules/applications/sitemap.class.php`
- `trickster-cms/homepage/modules/applications/ajax.class.php:10`
- `trickster-cms/cms/modules/applications/ajaxSearch.class.php` (public mode only)

**Leave alone:** `Crontab` (never had one), `Pouet`/`Sam`/`Zxdb` and the import
apps (`'crontab'`), `adminAjax` / `admin` / `ajaxSearch` in admin mode (`'admin'`).

`captchaApplication` is dead code — its `last_captcha` session write has one
reader (`cms/modules/validators/captcha.class.php:10`) and no form registers that
validator. Delete the application rather than de-sessioning it.

### 4.8 Legacy login modules — corrected

The earlier draft had this backwards. `project/` shadows `trickster-cms/cms/`, so
the admin login form runs the **project** copy. Deleting it would silently swap
the admin's login implementation.

- [ ] Keep `project/modules/structureElements/login/`; strip the `rememberUser()`
      call from `action.login.class.php:25` and the remember checkbox from
      `form.LoginForm.php`.
- [ ] Delete `trickster-cms/cms/modules/structureElements/login/` only after
      confirming nothing resolves to it (it is fully shadowed today).
- [ ] `LoginService::remember()` / `forget()` removed.

### 4.9 Server-rendered theme — corrected

The earlier draft conflated two mechanisms. `ThemeCodeProviderInterface` is the
**Smarty design-theme selector** (`public.class.php:12,44-45,190,261`), not the
user's dark/light preference; removing it leaves `$this->currentTheme` null and
the shell unrenderable. `project/modules/applications/simple.class.php:3` also
implements it.

- [ ] Remove only the `currentThemeClass` assign at `public.class.php:183`.
- [ ] Delete `project/core/ZxArt/UserPreferences/CurrentThemeProvider.php` (no
      other callers, no test).
- [ ] Leave `ThemeCodeProviderInterface` and `designThemesManager` untouched.
- [ ] `index.spa.tpl:2` already defaults to `dark-mode`; the SPA re-applies the
      stored theme synchronously.

### 4.10 Email verification auto-login

`project/modules/structureElements/registration/action.verifyEmail.class.php:33`
calls `$user->switchUser($userId, false)` — clicking the verification link logs
the account in as a side effect of the public session. That side effect
disappears.

Decision: **drop the auto-login.** Verify the account, then redirect to the SPA
with a "verified — please log in" state and let the user authenticate normally.
The alternative (mint a one-time code, redirect with it, exchange it at
`/auth-data/?action=exchange`) is more code and more attack surface for a
one-click convenience; do not build it unless the product owner asks.

- [ ] Same check for the password-reset landing flow — confirm it does not rely
      on a session either.

### 4.11 Config

`project/config/main.php` — add `'authTokenSecret' => ''` as a documented empty
default. The real value goes in the gitignored `project/config/live/main.php` on
**every** environment. `AccessTokenService` throws on an empty secret.

---

## 5. Endpoint contracts

All bodies are JSON. All errors are `{"errorMessage": "..."}` with a real status
code (`docs/php/rest-api.md:24`). No `responseStatus` envelope.

### 5.1 `POST /auth-data/?action=login`

```json
{"userName": "string", "password": "string", "remember": true}
```

`200`:
```json
{
  "accessToken": "eyJ1aWQ...",
  "tokenType": "Bearer",
  "expiresIn": 900,
  "user": {"id": 42, "userName": "moroz", "authorId": 1234}
}
```
Plus `Set-Cookie: zxrt=…; HttpOnly; SameSite=Strict; Path=/auth-data/[; Secure][; Max-Age=7776000]`

- `400` missing credentials · `401` bad credentials / unverified / banned
  (deliberately not distinguished) · `429` throttled, with `Retry-After` · `500`

### 5.2 `POST /auth-data/?action=refresh`

Empty body; credentials come from the `zxrt` cookie. `200` returns the same shape
as login (including `user`, so a page reload costs one round trip), plus a rotated
`Set-Cookie`.

- `401` missing / invalid / expired / revoked / reused — the response also clears
  the cookie (`Max-Age=0`)
- `429` throttled by IP

### 5.3 `POST /auth-data/?action=logout`

Revokes the presented refresh token only (other devices survive), clears the
cookie. `200 {}`. Idempotent.

### 5.4 `POST /auth-data/?action=logout-all`

Bearer required. Revokes every family of the user. `200 {}`.

### 5.5 `GET /auth-data/?action=sessions`

Bearer required. `401` for anonymous.
```json
[{"id": 12, "createdAt": 1754640000, "lastUsedAt": 1754643600,
  "current": true, "persistent": true, "ip": "1.2.3.4", "userAgent": "Firefox/…"}]
```

### 5.6 `GET /currentuser/`

Unchanged shape. Anonymous DTO when no/invalid bearer token. No session, no token
issuance.

### 5.7 OpenAPI

- [ ] New `api/auth.yaml` covering §5.1-5.5.
- [ ] `api/current-user.yaml` — remove the login/logout operations.
- [ ] `api/api.yaml` maintains a path index — register the new file there.
- [ ] Add a shared `bearerAuth` security scheme and reference it from the specs
      whose endpoints behave differently for authenticated users.

---

## 6. Frontend

### 6.1 `shared/services/access-token.service.ts` (new)

In-memory only (`BehaviorSubject<string | null>`) plus expiry. `token()`,
`set(token, expiresIn)`, `clear()`, `isExpiring()` (true within 30 s of expiry,
so the interceptor can refresh proactively instead of waiting for a 401). Never
touches `localStorage`.

### 6.2 `shared/services/auth-api.service.ts` (new)

Thin client for `/auth-data/` — `login`, `refresh`, `logout`, `logoutAll`,
`sessions`. All URLs keep the **trailing slash** (§1.2). `withCredentials: true`.

### 6.3 `shared/services/auth.service.ts` (new)

Single owner of auth state and the coordinator of refresh.

- `login(userName, password, remember)` → store token, publish user, broadcast.
- `logout()` → endpoint, clear, broadcast.
- `refresh()` — **cross-tab single flight**: wrapped in
  `navigator.locks.request('zx-auth-refresh', …)` where available, and
  deduplicated in-tab via `shareReplay({bufferSize: 1, refCount: true})`, so ten
  parallel 401s produce one network call and N tabs produce one rotation.
- `bootstrap()` — called by `authGuard` on app start; refreshes, and pushes the
  returned `user` straight into `CurrentUserService`'s store.

### 6.4 `shared/services/auth-broadcast.service.ts` (new)

`BroadcastChannel('zx-auth')` with a no-op fallback. Messages: `token`, `login`,
`logout`. Complements the Web Locks single-flight — locks prevent the race,
broadcasts spread the result.

### 6.5 `shared/interceptors/auth.interceptor.ts` (new)

Registered in `app.config.ts`: `withInterceptors([authInterceptor, languageInterceptor])`.

- Same-origin test must be
  `new URL(req.url, location.origin).origin === location.origin` — **not** the
  `^https?://` regex `languageInterceptor` uses. Three services build
  protocol-relative `//host/ajax/` URLs: `vote.service.ts:11`,
  `tags-search.service.ts:13`, `playlist.service.ts:16` (the entire playlist
  mutation surface).
- Skip `/auth-data/` (cookie-authenticated), the i18n bundles under
  `environment.assetsUrl`, **and `environment.svgUrl`** — `angular-svg-icon`
  fetches SVGs through `HttpClient`, and those are `Cache-Control: public` static
  assets (`htdocs/.htaccess`); an `Authorization` header would make shared caches
  refuse to serve them.
- Attach `Authorization: Bearer <token>` when a token exists; refresh first if
  the token is expiring.
- On `401`: refresh once, retry once with the new token. If refresh fails, clear
  state, broadcast logout, let the 401 propagate. Never retry a 401 that came
  from the retry.

### 6.6 `auth.guard.ts` — rewrite

```
token fresh? -> proceed
else         -> authService.bootstrap() -> proceed (always true)
```

`bootstrap()` calls `/auth-data/?action=refresh`, which returns `user` on success.

**This only pays off if `/currentuser/` stops firing on its own.**
`current-user.service.ts:21-26` is a `defer` that triggers `loadCurrentUser()` on
the first subscription by anyone, and the header, playlist buttons, editing
controls, delete buttons, collection/authors/parties pages and
`UserPreferencesService.initialize()` all subscribe. Without the change in §6.7
an anonymous visitor pays a failed refresh **plus** a `/currentuser/` call, and a
logged-in visitor risks `/currentuser/` firing before the token lands.

### 6.7 `current-user.service.ts` — changes

- `user$` stops self-loading. `AuthService.bootstrap()` becomes the only writer
  of the initial value; `user$` just exposes the store (still gated on
  `!== null`, so subscribers still wait for the first resolution).
- `login()` / `logout()` delegate to `AuthService`; the
  `/currentuser/?action=…` calls are removed.
- Subscribe to broadcast `login`/`logout` and update the store.
- `/currentuser/` remains for explicit re-reads (e.g. after a profile change).

### 6.8 `shared/guards/authenticated.guard.ts` (new)

Resolves the current user; if anonymous, redirects to `/` and opens the login
popover.

### 6.9 `shared/guards/anonymous.guard.ts` (new)

Redirects an authenticated user away from `/register` and `/password-reminder`.

### 6.10 `login-popover-content.component.ts`

- The `remember` checkbox stays; it now maps to cookie persistence. No UI change.
- Handle `429` → throttling message.
- New i18n keys in `ng-zxart/src/assets/i18n/{en,ru,es}.json`:
  `login.error-throttled`, `profile.logout-everywhere`, `profile.sessions.*`.
  The copies under `htdocs/` are build output — never edit them by hand.

### 6.11 `profile-page.component`

- "Log out everywhere" → `logoutAll()`.
- Optional phase 2: active-session list from `?action=sessions`.

### 6.12 `user-preferences.service.ts`

`initialize()` memoizes into `initialized$` and runs **once per app start** — it
does not currently react to a logout in its own tab either. Give it an explicit
`reset()` driven by the broadcast/logout event, so a logout (in any tab) drops
the previous user's preferences instead of leaving them until reload.

---

## 7. Which frontend views get guarded, and with what

Current state: only entity edit/create-with-id routes carry `editPrivilegeGuard`.
`/profile` and `/playlists` are reachable by anonymous users and merely fail with
a 401. `/…/add` routes without an `:id` carry no guard at all.

| Route | Guard | Why |
| --- | --- | --- |
| `/profile` | `authenticatedGuard` | `/profile-data/` 401s for anonymous (`ProfileData.php:73,83`) |
| `/playlists` | `authenticatedGuard` | `/playlists-data/` 401s for anonymous (`PlaylistsData.php:51-52`) |
| `/playlist/:id` | none | reads prods by link; works for anonymous |
| `/register` | `anonymousGuard` | `/register-data/` 409s for authenticated (`RegistrationService.php:38-41`) |
| `/password-reminder` | `anonymousGuard` | UX only — the endpoint has no such check |
| `/authors/add`, `/authors/:letter/add` | `authenticatedGuard` | create form, no id to check a privilege against |
| `/groups/add`, `/groups/:letter/add` | `authenticatedGuard` | same |
| `/parties/:year/add` | `authenticatedGuard` | same |
| `/prods/batch-upload` | `authenticatedGuard` | same |
| all `…/:id/{edit,add,join,split,ai}` | `editPrivilegeGuard` (unchanged) | already privilege-checked server-side |
| everything else | `authGuard` on the pathless parent (unchanged) | resolves identity before first render |

`editPrivilegeGuard` needs no change, but it must not run before the token
exists. Verified: it is a `canActivate` on a child of the pathless
`canActivateChild: [authGuard]` parent (`app.routes.ts:487-489`), and Angular
runs ancestor `canActivateChild` before child `canActivate`. Add a test for that
ordering — it is load-bearing and invisible.

**Components (not routes) that assume a logged-in user** — verify each renders
sensibly for anonymous and re-renders on broadcast login/logout:
`login-trigger`, `zx-right-column`, `zx-playlist-button`, `zx-editing-controls`,
`zx-delete-entity-button`, comment posting, vote buttons, tags quick form,
screenshot upload UI.

---

## 8. Crontab, imports and other non-browser callers

These must keep working with **no token and no session**.

| Caller | Entry | Session today | After |
| --- | --- | --- | --- |
| `ZxArt\Controllers\Crontab` | `/crontab/` | none | none — unchanged |
| `ZxArt\Controllers\Pouet` | `/pouet/` | `'crontab'` | keep |
| `ZxArt\Controllers\Sam` | `/sam/` | `'crontab'` | keep |
| `ZxArt\Controllers\Zxdb` | `/zxdb/` | `'crontab'` | keep |
| `project/modules/applications/{aaa,dmd,fix,fix-countries,coor,rzxArchive,s4e,speccyMaps,tslabs,vtrdos,zxPress}` | direct URLs | `'crontab'` | keep |
| `cms/modules/applications/VisitorRemove` | direct URL | **none** | none |

Corrections to the earlier draft: `project/modules/applications/parser.class.php`
is **not** a crontab caller — it starts no session, performs no user switch, and
is a **public SPA endpoint** (`features/parser/services/parser.service.ts:12`
posts uploads to `/parser/` from the `/file-search` page). `VisitorRemove` starts
no session.

Why the crontab path is safe: `switchUser()` writes `storage['currentUserId']` on
the in-memory `CurrentUser` and immediately re-runs `initialize()`, which reads
that array first. `Crontab` already proves session persistence is irrelevant by
never starting one.

Requirements to hold:

- [ ] `readUserId()` keeps `storage['currentUserId']` as the **first** branch.
- [ ] A `null` resolver result must not clear or override an explicit switch.
- [ ] The crontab apps set `setPrivilegeChecking(false)`, so privileges are off
      their hot path. Do not "optimise" that away.
- [ ] `Crontab.php:146` and `Pouet.php:46` call `$cache->enable(false, false, true)`
      on the shared `Cache` singleton. Confirm `PrivilegeCache` and any other new
      cache user tolerate reading being disabled mid-request.
- [ ] Nothing is invoked via php-cli — all of these are HTTP entry points.
      Re-verify if that changes.

Out of scope but recorded: `/crontab/` has **no access restriction of any kind** —
no session, no token, no IP allowlist, and the Apache vhost has no location
rules. Anybody can trigger a full AI/import run. Worth a follow-up ticket; do not
fold it into this migration.

---

## 9. Browser-direct loads — explicit decision required

`releasefile`, `zxfile`, `file`, `screenshot`, `zipItems`, `zximages` are
`<img src>` / `<a href download>` / `<audio src>` targets
(`zx-prod-files-list`, `zx-prod-screenshots-section:51,105`,
`zx-release-hero:93`, `zx-instruction-file-card:24`). They resolve content
through `structureManager::getElementById()`, and the public structure manager
has `privilegeChecking = true` (`structureManager.php:51,1043-1054,1263-1272`).

Today a logged-in editor carries the remember cookie on those GETs and can reach
elements an anonymous visitor cannot. After the migration those requests carry no
`Authorization` header and no cookie — **they become anonymous**.

- [ ] Audit whether any currently-linked download or image actually depends on a
      privilege. Public art, tunes, prods and screenshots do not.
- [ ] If none do: accept the downgrade, and record it here as a deliberate
      decision.
- [ ] If any do: issue a short-lived signed URL (`?t=<hmac>&e=<expiry>`) from the
      data endpoint that produced the link, and validate it in the serving
      application. Do not reintroduce a cookie.

Verified: the SPA uses no `fetch()`, `EventSource`, `WebSocket` or service
worker — all HTTP goes through `HttpClient`, so interceptor coverage is complete
for XHR. This section is the only gap.

---

## 10. Rollout order

Each phase is independently deployable and leaves the site working.

### Phase 1 — additive backend (no behaviour change)

- [ ] Migration `2026.08.08 - auth-tokens.sql`, applied; `DatabaseTable` cases added.
- [ ] `AccessTokenService`, `AccessTokenClaims`, `RefreshTokenRepository`,
      `LoginAttemptRepository`, `RefreshTokenService`, `RefreshCookie`,
      `BearerTokenReader`, `BearerUserIdResolver`, `LoginThrottle`,
      `AuthenticationService`.
- [ ] `AuthenticatedUserIdResolver` interface + null-object default + project binding.
- [ ] `authTokenSecret` in `project/config/main.php` (empty) and in
      `project/config/live/main.php` (real, on every environment).
- [ ] Apache `Authorization` forwarding (§4.2) — do this **before** Phase 2 or
      Phase 2 will appear to work in dev and fail in prod.
- [ ] `AuthData` controller; `api/auth.yaml`.
- [ ] Unit tests (§11).
- [ ] Verify by curl from inside the container (`docs/local-http.md`).

### Phase 2 — dual-mode identity

- [ ] `CurrentUser::setResolvedUserId()` + new `readUserId()` order; the remember
      cookie branch **stays** as a fallback for now.
- [ ] `CurrentUserService` resolver wiring.
- [ ] `EventsLog` switched to `CurrentUserService`.
- [ ] CORS blocks deleted from `ajax.class.php` / `ajaxSearch.class.php`.
- [ ] Both schemes work simultaneously; the frontend has not changed yet.

### Phase 3 — frontend switch

- [ ] `AccessTokenService`, `AuthApiService`, `AuthService`,
      `AuthBroadcastService`, `authInterceptor`.
- [ ] `app.config.ts` interceptor registration.
- [ ] `auth.guard.ts` rewrite; `authenticated.guard.ts`; `anonymous.guard.ts`.
- [ ] `current-user.service.ts` stops self-loading (§6.7) — same commit as the
      guard, or the app double-fetches.
- [ ] Route table updates (§7); component audit (§7, last row).
- [ ] `login-popover-content`, `profile-page`, `user-preferences.service`.
- [ ] i18n keys in all three languages.
- [ ] `composer build` (never `ng build`).

### Phase 4 — privilege cache

- [ ] `PrivilegeCache` + all six invalidation hooks (§4.6).
- [ ] `PlaylistService.php:82-83` missing `refreshPrivileges()` fixed.
- [ ] Measure query count for an authenticated page load before and after.

### Phase 5 — teardown

- [ ] Remove `startSession(...)` from the full list in §4.7.
- [ ] Delete `captchaApplication`.
- [ ] Remove `rememberUser()` / `forgetUser()` / the remember branch of
      `readUserId()`; `LoginService::remember()` / `forget()`.
- [ ] Remove login/logout actions from `Currentuser`.
- [ ] Strip remember from `project/modules/structureElements/login/`; delete the
      shadowed CMS copy (§4.8).
- [ ] Remove the `currentThemeClass` assign and delete `CurrentThemeProvider`
      (§4.9) — **not** `ThemeCodeProviderInterface`.
- [ ] Email-verification auto-login removed (§4.10).
- [ ] Expired-token and stale-attempt purges added to the crontab sweep.
- [ ] Documentation (§12).

---

## 11. Tests

### PHP (`tests/Auth/`)

- [ ] `AccessTokenServiceTest` — round trip; tampered payload; tampered
      signature; expired; skew tolerance; **empty secret throws**.
- [ ] `RefreshTokenServiceTest` — issue/rotate happy path; wrong secret; unknown
      id; expired; revoked; **replay inside the window returns the same
      successor**; **replay outside the window revokes the family**;
      `revokeAllForUser`; absolute family expiry is not extended by rotation.
- [ ] `RefreshTokenRepositoryTest` / `LoginAttemptRepositoryTest` — Query Builder
      mocks returning arrays, per `docs/testing.md`.
- [ ] `RefreshCookieTest` — flags, path, `Max-Age` only when persistent, `Secure`
      only under https, trailing-slash path.
- [ ] `LoginThrottleTest` — per-IP and per-user counters, window expiry, reset on
      success.
- [ ] `AuthenticationServiceTest` — login success/failure/throttled; refresh
      success/invalid; **refresh returns the real user, not `anonymous`** (§3.6);
      logout idempotent.
- [ ] `BearerTokenReaderTest` — `HTTP_AUTHORIZATION`,
      `REDIRECT_HTTP_AUTHORIZATION`, missing, malformed, wrong scheme.
- [ ] `PrivilegeCacheTest` — hit, miss, invalidate, disabled-cache no-op.
- [ ] Regression: `CurrentUser` prefers `storage['currentUserId']` over a
      resolved token id (the crontab guarantee).
- [ ] `tests/Users/ServerSessionManagerTest.php` must still pass unchanged.

### Angular

- [ ] `auth.interceptor.spec.ts` — header attached; `/auth-data/` skipped;
      `assetsUrl` and `svgUrl` skipped; protocol-relative `//host/ajax/` treated
      as same-origin; one refresh for N parallel 401s; no retry loop; failed
      refresh clears state.
- [ ] `auth.service.spec.ts` — in-tab dedup; cross-tab lock; broadcast on
      login/logout.
- [ ] `auth.guard.spec.ts` — always activates; token present before child guards
      run; `/currentuser/` is **not** fired during bootstrap.
- [ ] `authenticated.guard.spec.ts` / `anonymous.guard.spec.ts`.
- [ ] `user-preferences.service.spec.ts` — resets on logout.

### Manual, in the container (`docker compose exec -T app curl …`)

- [ ] Login → token works on `/playlists-data/`.
- [ ] Expired token → 401 → refresh → retry succeeds.
- [ ] Same cookie refreshed twice inside 60 s → both succeed, same successor.
- [ ] Same cookie refreshed twice after 60 s → family revoked, both subsequent
      refreshes 401.
- [ ] Logout → refresh 401, cookie cleared.
- [ ] Two tabs: log out in one, the other goes anonymous without reload.
- [ ] Two tabs opened simultaneously after a long idle: both recover, one
      rotation.
- [ ] Form save through `/ajax/` works while logged in (the largest single
      regression risk).
- [ ] Playlist add/remove, vote, tag search all still authenticate (the three
      protocol-relative URL services).
- [ ] `/crontab/`, `/pouet/`, `/sam/`, `/zxdb/` still run as the `crontab` user.
- [ ] Anonymous page load creates no session file under the sessions cache path.
- [ ] `Authorization` header actually arrives in PHP on the production vhost.
- [ ] `composer test` and `composer psalm` — the psalm baseline is known-broken
      locally, so compare against the pre-change run rather than zero.

---

## 12. Documentation to update

- [ ] `docs/domain/language-auth.md` — rewrite "Auto-login": the SPA bootstraps by
      refreshing, not by a remember cookie.
- [ ] `docs/cms/sessions.md` — the public site no longer uses sessions; the
      document narrows to the admin panel and the crontab session name.
- [ ] New `docs/domain/authentication.md` — token formats, lifetimes, rotation,
      replay window, reuse detection, throttling, revocation, multi-tab. Current
      state only, no history (`AGENTS.md` rule).
- [ ] `docs/cms.md:132` **and `:134`** — privileges are cached in `Cache`, not in
      the session; `refreshPrivileges()` clears the cache, not session storage.
- [ ] `docs/angular.md` — the auth interceptor and the guard set.
- [ ] `api/auth.yaml` new; `api/current-user.yaml` trimmed; `api/api.yaml` index
      updated.
- [ ] `git add` every new file immediately on creation (`AGENTS.md` rule).

---

## 13. Risk register

| Risk | Mitigation |
| --- | --- |
| Apache strips `Authorization` → everyone silently anonymous in prod | §4.2 is a Phase-1 checklist item, verified on the real vhost before Phase 2 |
| Base-controller change breaks 79 subclasses | Resolution moved into `CurrentUserService`; no controller signature changes (§4.1) |
| Multi-tab rotation logs the user out | Web Locks single-flight + 60 s server-side replay cache + `FOR UPDATE` transaction (§1.3) |
| Grace window silently disables theft detection | Replay cache returns the *same* successor; family never forks (§1.3, rejected alternative) |
| `EventsLog`'s DI-injected `CurrentUser` keeps the old identity | Switched to `CurrentUserService` in Phase 2 (§4.1) |
| Group changes take an hour to apply | Six invalidation hooks, including the four `userRelation` write sites (§4.6) |
| `Cache` disabled → throttle silently off | Throttle is DB-backed, not cache-backed (§1.6) |
| `/ajax/` misses authentication → every form save degrades | Covered automatically by §4.1; explicit manual test in §11 |
| Downloads and images lose privileged access | §9 requires an explicit decision before Phase 5 |
| `/currentuser/` self-load defeats the round-trip saving | §6.7 ships in the same commit as §6.6 |
| Secret missing on an environment | `AccessTokenService` throws on an empty secret |
| Admins lose the remember cookie | Intended; announce it. Admin session lifetime is already 86400 s |
| XSS reads the access token from memory | 15 min TTL bounds the damage; the refresh credential is httpOnly and unreachable from JS |
