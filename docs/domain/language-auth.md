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
current-user response.

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
