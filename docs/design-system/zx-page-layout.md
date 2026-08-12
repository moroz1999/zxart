# zx-page-layout

`zx-page-layout` is the required outer layout for routed pages. It provides a
`zxPageHeader` slot for the page title and page-level controls, followed by the
default slot for any number of independent content blocks.

Every routed page renders one `<h1 appHeading="display" zxPageHeader>`. Page
content blocks do not add external margins; `zx-page-layout` **owns** the gap
below the header and the vertical rhythm between content blocks
(`--zx-page-layout-content-block-gap`), and resets margins on its direct content
children.

The header reserves the title box before the title is known
(`--zx-page-layout-header-min-height`), because an entity page renders its `<h1>`
empty until the entity loads. The reservation is
`--zx-page-layout-header-lines` display lines: one on desktop, two below `md`,
where a title that fits a single desktop line usually wraps. Without it the
whole page moves down at the moment the title arrives.

```html
<zx-page-layout>
  <h1 zxPageHeader appHeading="display">Title</h1>
  <zx-feature-one></zx-feature-one>
  <zx-feature-two></zx-feature-two>
</zx-page-layout>
```

## Owning the rhythm across a feature boundary

The page-level rhythm lives in **one** place: `zx-page-layout`'s content area.
The generic layout primitives (`zx-stack`, `zx-grid`, …) know nothing about
"page" spacing — that concern belongs to the page layout alone.

Most routed pages render their content through a single feature-view component
(e.g. `zx-author-details-view`). If that host were a normal block, its inner
blocks would be hidden from the layout and the between-block gap would never
apply. To keep `zx-page-layout` the sole owner of the rhythm, a feature-view
**dissolves its own host** so its blocks become direct flex items of the
layout's content area:

```scss
:host {
  display: contents; // host box disappears; children join the layout's flex
}
```

The feature then exposes a **small number of real page blocks** — not a flat
list of every element. Tightly-coupled top matter (hero/header + action bar) is
grouped into a single **cluster** block with its own tight `zx-stack`; the rest
(tabs, or the section body) are separate blocks. The layout supplies the larger
gap *between* those blocks; the feature owns the tight spacing *inside* each.

```html
<!-- inside a feature-view, :host { display: contents } -->
<zx-stack spacing="xl"><!-- cluster block: tight top matter -->
  <zx-author-header …></zx-author-header>
  <zx-editing-controls …></zx-editing-controls>
</zx-stack>

<zx-tabs …></zx-tabs><!-- next page block; layout adds the gap before it -->
```

The breadcrumb trail is not part of a page: `zx-breadcrumb-bar` renders it once
in the shell, directly above the layout.

For a dense body (picture/tune/release/prod) the pattern is **cluster + body**:
the top matter is one cluster block and all content sections stay together in a
single body `zx-stack` (preserving their tighter internal rhythm); the layout
adds the page gap only between the cluster and the body.
