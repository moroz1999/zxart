# Categories — implementation

Domain rules: [../domain/category.md](../domain/category.md).

Categories are `zxProdCategory` elements under the `zxProdCategoriesCatalogue`
root. A prod stores its categories as a `numbersArray` of element ids.

`ZxArt\ZxProdCategories\CategoryIds` names the ids code depends on, split into
the **top-level sections** (`GAMES` 92177, `DEMOSCENE` 204819, `PRESS` 244858,
`SYSTEM_SOFTWARE`, `EDUCATIONAL`, `SERIES`, `MISC`) and the **leaves inside
them** (`DEMOS` 92159, `MEGADEMO`, `TRACKMO`, `PRESS_MAGAZINES`, the compilation
categories). Sections are the filter roots; leaves exist to file a production
under a precise category and must never be filtered by.
`CompilationCategoryIds` names the categories that make a prod a compilation and
enable its `compilationItems`.

## Expanding a category

**Filtering by a category always means its whole subtree.**
`ProdCategoryTreeService::getTreeIds()` resolves one category to every id
beneath it, and `expandAll()` does the same for a list. Everything that narrows
by category goes through it — the radio, the music collection filters and the
firstpage prod modules — so nobody re-implements the walk and picks the wrong
root. Group prods, stats and the prods list still walk
`zxProdCategoryElement::getSubCategoriesTreeIds()` directly.

Matching a section's own link alone drops most of what belongs to it: the
firstpage "best new demos" module used `DEMOS` and so never showed a megademo,
an intro or a trackmo.

## Public URLs

The software catalogue route is `/prods`, and a selected category lives in the
`cat` query parameter (`/prods?cat={categoryId}`). Legacy catalogue and category
paths redirect permanently to it.

`ZxProdsCategoryComponent` renders a category's prods and supports the
screenshots, inlays and table layouts. The catalogue response is built by
`zxProdCategoriesCatalogueDataResponseConverter`.
