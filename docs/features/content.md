# Static content pages — implementation

Domain rules: [../domain/content.md](../domain/content.md).

The About-section editorial pages are static and non-editable: the HTML is bundled
on the backend and rendered by the SPA in a plain container.

- Pages: `about`, `faq`, `support`, `api` — standalone SPA routes `/about`,
  `/about/faq`, `/about/support`, `/about/api` (`pages/content` → `ContentPageComponent`).
  The title comes from the menu i18n key (route data); the body is fetched HTML,
  rendered through `bypassSecurityTrustHtml` — the copy ships with the backend rather
  than coming from users, and the sanitizer would otherwise drop the `id`/`name`
  anchors the FAQ's table of contents links to.
- Endpoint: `GET /content-data/?page=<page>&lang=<code>` (`ZxArt\Controllers\ContentData`
  → `ZxArt\Content\ContentService`). Spec: `api/content.yaml`.
- The frontend `ContentService` derives `lang` from `LanguageService.current$`, so the
  page re-fetches and re-renders when the interface language is switched.
- The FAQ's table of contents relies on plain `#anchor` links, which the browser
  resolves against the document base URL. The SPA shell therefore carries no `<base>`
  element — see the base href note in [../angular.md](../angular.md).
- Content lives in `project/core/ZxArt/Content/pages/<page>.<lang>.html`. Pages are
  whitelisted; a missing translation falls back to English. Edit these files to change
  the copy — there is no admin UI.
- Translations present: `faq` in `en`/`ru`, `support` in `en`/`ru`/`es`, `api` in
  `en`/`ru`/`es`. The rest resolve through the English fallback; adding a translation
  means adding the matching `<page>.<lang>.html` file and nothing else.
- The `api` page documents the public export API. Its field lists come from the `api`
  preset of each `project/modules/dataResponseConverters/*` converter, its filter lists
  from `project/modules/queryFilters/`, and its sorting fields from the `columnRelations`
  of the matching service — keep the three language files in sync when those change.
