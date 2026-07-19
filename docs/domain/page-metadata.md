# Public Page Metadata

The public SPA uses the same metadata object shape for the initial server shell
and entity core responses. Entity metadata is resolved from the CMS metadata,
OpenGraph, Twitter, language-link, and JSON-LD providers.

The server shell renders title, description, robots, OpenGraph, Twitter,
alternate-language, and JSON-LD data so crawlers receive it without executing
JavaScript. Canonical links are not emitted.

Angular applies entity metadata from the entity's existing core request. Fixed
routes use Angular translation keys without an additional request. RSS discovery,
favicon, HTML language, and theme color are
part of the initial shell. Yandex Metrika is initialized by Angular and records a
page view after metadata has been applied for each navigation.

Historical public URLs are resolved on the backend and redirected to clean SPA
URLs with HTTP 301. Unknown public URLs are logged by the backend and return HTTP
404 while rendering the Angular not-found shell.
