# Entity URLs in API Responses

Every URL an API response emits for a page of this site is a clean SPA route:
`/prod/123`, `/release/456`, `/author/42`, `/authors/l`. Legacy structure URLs
(`/rus/soft/games/…/`) must never leave a service — the SPA feeds these fields
straight into `routerLink`, and the router resolves a legacy path relative to the
current route, producing a URL that belongs to no page at all.

`ZxArt\Urls\EntityUrlResolver` is the only place that maps an element to its
page:

- `resolve()` / `urlFor()` — the route of a routed entity or section, falling
  back to the element's legacy URL only for types that have no SPA page.
- `urlForUser()` — a user's public page is the author page they are connected
  to, so an account without an author gets `null`. Submitter fields are nullable
  for that reason, and the SPA renders the name as plain text when it is.
- `absoluteUrlFor()` — the same route with the site's base URL in front, for
  addresses that leave the site: `og:url` and ld+json (`CanonicalUrlTrait`).
  Canonical metadata names the URL that renders the page, not the legacy path
  that redirects to it.

Two kinds of URL fields are deliberately not SPA routes and stay as they are:
files served by a legacy download application (`/releasefile/`, `/zxfile/`,
`/screenshot/`, `/image/`, `/zipItems/`) and links to other sites.

Sections have no element of their own to resolve: an author's or group's page
builds its breadcrumb trail from `/authors` and `/groups` plus the alphabet
folder's name (`/authors/l`), taking only the titles from the legacy ancestors.
