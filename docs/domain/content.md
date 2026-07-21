## Static content pages

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
- Content lives in `project/core/ZxArt/Content/pages/<page>.<lang>.html`. Pages are
  whitelisted; a missing translation falls back to English. Edit these files to change
  the copy — there is no admin UI.
- Translations present: `faq` in `en`/`ru`, `support` in `en`/`ru`/`es`, `api` in `en`
  only. The rest resolve through the English fallback; adding a translation means adding
  the matching `<page>.<lang>.html` file and nothing else.
