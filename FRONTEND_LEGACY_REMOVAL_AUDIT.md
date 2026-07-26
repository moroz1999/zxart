# Public Frontend Migration Status

## Current Architecture

The public body is Angular-only. Smarty renders the initial document shell with
`<app-root>`, Angular bundle assets, and server-side crawler metadata. No public
legacy CSS, LESS, or JavaScript bundle is loaded.

The backend retains HTTP responsibilities that cannot be reproduced reliably by
a client-only SPA:

- clean SPA route recognition;
- HTTP 301 redirects from historical structure URLs;
- configured redirect rules;
- HTTP 404 status and 404 logging;
- initial title, description, robots, OpenGraph, Twitter, hreflang, and JSON-LD;
- RSS discovery, favicon, HTML language, and theme color.

Canonical links are intentionally not emitted.

## Client Navigation

Angular requests a typed `PageMetadataDto` from `GET /page-metadata/?path=...`
after every completed navigation. `PageMetadataService` updates title,
description, robots, OpenGraph, Twitter, hreflang, and JSON-LD. Canonical links
are outside the metadata contract.

`LanguageService` owns `document.documentElement.lang` after bootstrap.

## Analytics

Angular initializes Yandex Metrika counter `94686067`. Automatic initial
tracking is deferred, and one explicit page view is sent after route metadata is
applied. Existing `reachGoal()` calls use the same initialized counter.

Google Analytics and Google Ads are not loaded.

## Legacy Assets

`projectDesignTheme` does not register `project/css/public` or
`project/js/public`. The physical files remain until all non-SPA public actions
and forms have been audited. Admin and non-public applications are outside this
cleanup.

Roboto fonts are owned by the Angular build. Material Icons font loading is not
restored.

## Verification Checklist

- [x] Angular production build succeeds.
- [x] `SpaRouter` unit tests cover canonical, action, collection, and legacy URLs.
- [x] PHP syntax checks pass for the changed backend files.
- [x] Server shell contains robot metadata and no canonical link.
- [x] RSS, favicon, HTML language, and theme color are present in the shell.
- [x] Yandex is initialized by Angular; Google scripts are absent.
- [ ] Verify a historical URL returns one HTTP 301 in the configured application environment.
- [ ] Verify an unknown URL and a missing entity ID return HTTP 404 and are logged.
- [ ] Verify representative entity and collection pages with a crawler-style request.
- [ ] Verify Yandex initial and client-navigation page views in the browser.
- [ ] Audit remaining public action/form URLs before deleting physical legacy assets.

The local `zxart.loc` virtual host is not connected to this workspace instance,
so the pending HTTP/browser checks require the configured Docker or web-server
environment.
