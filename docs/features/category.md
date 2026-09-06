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

## Managing the tree

The tree itself hangs under the single `zxProdCategories` container by
`structure` links; the top-level categories carry a second, `softCatalogue` link
from each language's software folder, and that is what the public catalogue
lists. `ZxArt\ZxProdCategories\CategoryManageService` maintains both: a
top-level category is created under the container and linked into every
catalogue folder, a subcategory is an ordinary child of its parent.

It is edited at `/manage/categories` through `/categories-data/`, behind the
category's own element actions — `zxProdCategory/receive` to save one,
`zxProdCategory/delete` to remove one, held by the `zx-categories-managers`
group; see [manage-section.md](manage-section.md). Deletion cascades down the
`structure` links, so it is refused while the category still holds productions
or subcategories: the editor empties the branch first.

Titles are edited in every interface language at once, and a blank one is
refused — a category with no title in a language shows a blank label to that
whole audience.

The container lives under the admin root, outside the public URL tree, so the
public structure manager reaches it by id rather than by path, and only for a
user whose group holds `zxProdCategories`/`showFullList` on the public root.
Without that type privilege no element of that type is ever instantiated there,
and a top-level category could not be created at all.

Each row prints **two** counts: the productions filed on the category itself,
and what its whole branch holds. A section carries few of its own and all of its
genres', so either number alone misreads the tree — "Demoscene" holds 6203
directly and 13550 across its subcategories.

### Moving a category

`parentId` states where a category belongs on every save, creation and update
alike, and `null` is the top level. A move re-links it under the new parent and
maintains the catalogue projection in the same step: it gains its
`softCatalogue` links when it becomes top-level and loses them when it stops
being one, because that projection is the only reason the catalogue lists it.

Two consequences worth knowing:

- **A category cannot be moved into its own subtree.** The request is refused
  with a 409 rather than orphaning the branch, and the management form does not
  offer those options at all.
- **A moved category lands last** among its new siblings. A link's position is
  assigned as "after the last one", so ordering within a parent stays the admin
  panel's positions screen to set.

Nothing recalculates the projection on its own. The admin catalogue form
(`receiveZxProdCategoriesCatalogue`) rebuilds it wholesale for every top-level
category, and that is the only other place it is written; there is no job to run
after a change here.

A link written straight through `linksManager` does not clear the parent's
cached copy — only `persistStructureLinks()` does — so every write that touches
the projection or moves a category drops the affected elements from the element
cache itself.

## Public URLs

The software catalogue route is `/prods`, and a selected category lives in the
`cat` query parameter (`/prods?cat={categoryId}`). Legacy catalogue and category
paths redirect permanently to it.

`ZxProdsCategoryComponent` renders a category's prods and supports the
screenshots, inlays and table layouts. The catalogue response is built by
`zxProdCategoriesCatalogueDataResponseConverter`.
