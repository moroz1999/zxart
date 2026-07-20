# zx-page-layout

`zx-page-layout` is the required outer layout for routed pages. It provides a
`zxPageHeader` slot for the page title and page-level controls, followed by the
default slot for any number of independent content blocks.

Every routed page renders one `<h1 appHeading="display" zxPageHeader>`. Page
content blocks do not add external margins; `zx-page-layout` owns the gap below
the header and the gap between content blocks, and resets margins on its direct
content children.

```html
<zx-page-layout>
  <h1 zxPageHeader appHeading="display">Title</h1>
  <zx-feature-one></zx-feature-one>
  <zx-feature-two></zx-feature-two>
</zx-page-layout>
```
