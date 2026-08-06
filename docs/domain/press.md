# Press articles

A press article (`pressArticleElement`) is one article of a disk magazine. Its
parent is always the `zxProdElement` of the issue it was published in, so an
issue's `articles` property is that issue's table of contents, in publishing
order.

## Storage

| Field | Meaning |
|---|---|
| `title` | Short table-of-contents label |
| `h1` | Descriptive article title; falls back to `title` |
| `introduction` | Short summary (HTML) |
| `content` | The article text (HTML), typed against a fixed-width grid |
| `externalLink` | Source the text was taken from |

`originalContent` is the raw text the article was typed or pasted from, before
the AI formats it, translates it into the site languages and writes the
introduction. It has no column of its own: it lives one row per article in
`module_pressarticle_archive` (`PressArticleRepository`) and is stored exactly
as submitted — `publicReceive` archives the posted text itself, bypassing the
`html` chunk that would purify it, and the form templates escape it on output.
`pressArticleElement::getOriginalContent()` reads it back, and the element puts
it into its own `getFormData()` so both the SPA and the legacy form show what is
archived. It is empty until something is archived; the article's `content` is
never offered in its place — that text is the AI's output, not a source.

`getH1()` prefixes the publication title (`Spectrofon #14: Readers' letters`)
and is what the document title and the page `<h1>` use. `getShortTitle()`
returns the same title without that prefix.

Articles link the entities they mention through their own link types: `authors`,
`people`, `groups`, `software`, `pictures`, `tunes`, `parties`.

## `/press-details/?id=`

`PressDetailsService` builds the whole read view in one response
(`api/press-details.yaml`):

- `content` — the processed article text, or the archived original when the AI
  has produced none in the current language (`isContentOriginal()`). Both are
  markup: an original is normally the magazine text wrapped in `<span
  class="RGB…">` runs that carry its colours, so the page renders either the
  same way;
- `title` — `getH1()` (`<publication>: <article>`), the document title and the
  page `<h1>`;
- `shortTitle` — the article's own title, for the breadcrumb and the issue's
  table of contents;
- `publication` — the issue: title, url, year, cover `imageUrl` and `articles`,
  the issue's full table of contents.

The table of contents carries the neighbouring articles, so the previous/next
links are derived in the SPA from the current article's position in it — there
are no separate navigation fields.

Title, introduction and content are multi-language fields, so
`PressDetailsApiService` keeps the request subscribed to `languageCode$` and
refetches the article when the interface language changes.

## Read page (`/press/:id`)

`zx-press-details-view` renders the article inside `zx-sidebar-layout`:

- **sidebar** (`zx-press-issue-nav`) — the issue cover, its title and year, the
  table of contents (`zx-popover-menu-item` rows in a `contentBleed` panel, the
  current article marked `active`) and a link to the whole issue. The contents
  list and that link are rendered on desktop only: below `lg` the sidebar sits
  above the article and stays short;
- **content**, in this order — the source link and the tags as labelled
  `zx-meta-row`s (the issue and its year are already named by the `<h1>` and the
  sidebar, so they are not repeated here); the mentioned entities as
  `zx-meta-row` + `zx-chips` rows; the introduction as a labelled `zx-meta-row`;
  the article text in `zx-preformatted` (`[html]="true"` — the stored text
  carries its own markup); previous/next article callouts; and the shared
  `zx-comments-list-view`.

The page is a reading surface, not a catalogue entity page: it opens with the
plain heading and meta line instead of a `zx-hero`, and its panels are
`variant="flat"` — hairline border, no elevation — so nothing competes with the
article text. `zx-sidebar-layout` caps the reading column at its content
measure.

`zx-press-editing-controls` uses the `popover` presentation and shares one
`zx-inline zxPageHeader` with the `<h1>`, so the edit and AI actions sit behind
a single icon trigger right after the title instead of at the far end of the
header row.

The breadcrumb trail is published through `BreadcrumbService`:
software → press category → issue → article.
