# Interface language & auto-login

The SPA owns the interface language. There is no language segment in SPA URLs and
no reliance on the backend session language.

## Language model (frontend)

The interface language is a **user preference** (`PreferenceCode::LANGUAGE`,
iso6393 value), so it flows through `UserPreferencesService` exactly like the
theme. `LanguageService` (`features/settings/services/language.service.ts`) is the
single owner and mirrors `ThemeService`:

- The supported languages are a frontend constant (`SUPPORTED_LANGUAGES`) matching
  the shipped i18n bundles. Each entry maps a short code (`en`/`ru`/`es`, used by
  ngx-translate) to a backend iso6393 code (`eng`/`rus`/`spa`), plus a native
  title and flag.
- `initialize()` applies the stored language immediately (the `language`
  preference from localStorage, or English by default), then calls
  `UserPreferencesService.initialize()` and re-applies the value fetched for a
  logged-in user. It registers the langs, sets the default lang, and calls
  `translate.use()`.
- `setLanguage(short)` (switcher) applies the language and persists it via
  `UserPreferencesService.setPreference('language', …)` — which stores it in
  localStorage for anonymous visitors and PUTs it to the backend for logged-in
  users. Anonymous visitors never send anything to the backend.
- `languageCode$` exposes the current iso6393 code; language-scoped API callers
  (comments, backend links) subscribe to it.

Because the language is a normal preference, a logged-in user's stored language
arrives through the usual `/userpreferences/` fetch — it is **not** carried on the
current-user response. Both frontend and backend preference defaults use `eng`.

## Preference storage

`UserPreferencesService.initialize()` runs once per app start and is the single
entry point every preference owner waits on:

- The in-memory store and localStorage mirror use one object keyed by preference
  code. Backend DTO arrays are converted only at the HTTP boundary.
- A logged-in user always has the preferences refetched from `/userpreferences/`,
  and the response overwrites localStorage — another device may have changed them
  since. If the request fails, the stored values stand in.
- Anonymous visitors have nothing to fetch. Frontend defaults are merged with
  their localStorage overrides and written back as one complete local snapshot;
  reads, writes, and resets never reach the preferences backend.
- Logged-in writes update the backend first. Only a successful response updates
  the in-memory preference store and its localStorage mirror, so failed requests
  cannot leave the browser ahead of the backend.
- Every successful mutation emits the complete preference snapshot through
  `preferences$`; consumers derive their current state from that stream instead
  of maintaining separate reload signals.
- Preferences stored for a different user are dropped before anything is read,
  which is what clears them after a logout.

Owners (`ThemeService`, `LanguageService`, `PictureSettingsService`) apply the
stored value synchronously first and re-apply once `initialize()` resolves, so a
preference set elsewhere wins without delaying the first render.

## Language on API requests

`languageInterceptor` (`features/settings/interceptors/language.interceptor.ts`)
adds an `X-Language` header (iso6393) to every same-origin request. The base
controller `LoggedControllerApplication` reads `X-Language` in its constructor and
applies it via `LanguagesManager::setCurrentLanguageCode()`, so every SPA data
endpoint returns localized content in the selected language, independent of the
URL or session.

## Auto-login

Auto-login is driven from the frontend. `authGuard`
(`shared/guards/auth.guard.ts`) is a blocking `canActivateChild` on a pathless
parent wrapping all routes. It resolves `CurrentUserService.user$` before the
first render; the request to `/currentuser/` makes the backend restore the session
from the remember cookie. Activation always proceeds — the guard only blocks the
first render until the user is known.

## Loading a user element

User elements live under the `users` catalogue, which regular accounts cannot
walk to from the site root. `structureManager::getElementById($userId)` therefore
returns `null` for them — always pass `directlyToParent`:
`getElementById($userId, null, true)`.

The account's own author is referenced by the `authorId` field on the user, not
by a structure link. Code that removes or absorbs an author must re-point that
field (see [authors-groups.md](authors-groups.md#merging-authors-and-groups)).

`/currentuser/` passes that id through as `authorId`, and the user popover builds
its "my page" link as `/author/{authorId}`. Like every other response, it carries
identifiers rather than routed URLs.

## Account self-service

`/profile` shows the account name and email read-only and offers exactly one
change: the password. Everything else about a user (contact details, groups,
privileges, verification and ban flags) is admin-only.

Changing the password requires the current one, so an unattended session cannot
be turned into a permanent takeover. The `password` data chunk hashes whatever
is assigned to `userElement::$password` — never hash before assigning. Saving
bumps `dateModified`, which invalidates outstanding reset links.

## Password recovery

The public `/password-reminder` Angular route requests and applies password-reset
links through `POST /password-reminder-data/`. The endpoint returns the same
successful request response whether or not the email belongs to an account.

Reset tokens are timestamped HMAC values with a one-hour lifetime. The user's
current password hash is the HMAC key, and the signed payload includes the
account id, normalized email, `dateModified`, and issue time. Changing the
password or `dateModified` therefore invalidates every outstanding token.
Password-reset links use the application's configured public `baseURL`.

## Registration

`POST /register-data/` creates public accounts through `RegistrationService`.
Invalid fields return 422, duplicate accounts and already-authenticated requests
return 409, and successful creation returns 201. The service applies the
registration element's default groups and sends its verification email.
