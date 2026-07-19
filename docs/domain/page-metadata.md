# Public Page Metadata

The public SPA uses one backend metadata object for both the initial server shell
and client-side navigation. Entity metadata is resolved from the CMS metadata,
OpenGraph, Twitter, language-link, and JSON-LD providers.

The server shell renders title, description, robots, OpenGraph, Twitter,
alternate-language, and JSON-LD data so crawlers receive it without executing
JavaScript. Canonical links are not emitted.

Angular requests `GET /page-metadata/?path={route}` after navigation and updates
the same head fields. RSS discovery, favicon, HTML language, and theme color are
part of the initial shell. Yandex Metrika is initialized by Angular and records a
page view after metadata has been applied for each navigation.

Historical public URLs are resolved on the backend and redirected to clean SPA
URLs with HTTP 301. Unknown public URLs are logged by the backend and return HTTP
404 while rendering the Angular not-found shell.
