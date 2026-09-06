# Management section — implementation

The catalogue upkeep screens the Angular SPA serves at `/manage`. `/admin`
belongs to the legacy Smarty panel, which these screens replace one by one (see
[../cms.md](../cms.md)).

## Sections

| Tab | Route | Save / delete privilege | Endpoint | Detail |
|---|---|---|---|---|
| Hardware | `/manage/hardware` | `editHardware` | `/hardware-data/` | [hardware.md](hardware.md) |
| Categories | `/manage/categories` | `zxProdCategory/receive`, `zxProdCategory/delete` | `/categories-data/` | [category.md](category.md) |
| Countries | `/manage/countries` | `country/receive`, `country/delete`, `city/receive`, `city/delete` | `/countries-data/` | [geo.md](geo.md) |

## Privileges are element actions

Categories, countries and cities are ordinary structure elements and already
carry a full set of per-type action privileges, so the screens ask for **the
action they perform** — the same `receive` and `delete` the Smarty forms have
always been gated on — rather than a privilege invented for the screen.
`editHardware` is the one exception and stays one: the hardware catalogue is a
plain table with no element behind it.

An action privilege is asked for in `type.action` form and resolved **on the
public root**, whose id comes from `/currentuser/`. Two things follow:

- The existing grants sit on the *admin* root, which is the tree the Smarty
  panel loads elements under. A public request compiles privileges under the
  public root, so the same actions have to be granted there as well for the SPA
  to see them — which is what the section's own migration does.
- Holding an action on the root means holding it for everything below, so the
  check is "may this user edit categories at all", not "may they edit this one".

Saving and deleting are separate privileges, and a country and a city are
separate types, so a screen may offer a save and withhold a delete. The tabs
list a section to whoever may **save** in it; each delete button asks for its own
privilege.

`rootPrivilegeGuard` keeps a section out of sight of a user who cannot use it and
the endpoint refuses the write regardless — the guard is convenience, not the
gate. `RootPrivilegeService` asks for each privilege once per session.

`ZxManageTabsComponent` (`features/manage/`) renders the tabs and leaves out a
section the user holds no privilege on. The tabs are routed links, so the active
one follows the URL and an edit route keeps its section's tab active.

## Who holds them

Two groups exist for the two element-backed sections — `zx-categories-managers`
and `countries-managers` — and the migration creates them **empty**. Membership
is a decision for whoever runs the site, made by adding users to the group in the
admin panel, not by a migration. `privilegesManager` reads `$user->privileges`
from the session, so someone added to a group sees the screen after their next
login.

## Shape of a section

The three screens are deliberately the same shape, and a fourth should follow it:

- **One endpoint per section**, named `<Thing>Data` so the SPA route of the same
  name stays free. `GET` returns the whole list — these are hundreds of rows, not
  thousands — with every interface language's labels in it, because the list and
  the edit form are answered by the same request. `POST ?action=…` writes and
  answers with the refreshed list, so the client replaces its copy instead of
  patching it.
- **A store per section** (`features/manage-*/services/`), holding that response
  in a `BehaviorSubject` and replacing it from every write. HTTP errors are not
  swallowed there: the screen has to show why a save or a delete was refused.
- **The list page owns deletion**, behind a `ConfirmDialogService` dialog, and
  the edit page owns nothing but the form. What may not be deleted is decided by
  the backend and reported as a `409` the page prints.
- **Where an entity belongs is an ordinary form field**, not a hidden one: the
  category's parent and the city's country travel with every save, so the same
  select that places a new entity moves an existing one. A creation started from
  a row opens with that row preselected (`?parent=`, `?country=`).

Categories and countries both render `zx-tree`; a country expands into its
cities exactly as a category expands into its subcategories.

## Projections

A top-level category and a country are also **projected** into each language's
branch — a category by a `softCatalogue` link from the software folder, a
country by a `countries` link from the geo section — and that projection is what
the public catalogue and the geo section list. The admin forms of those
container elements rebuild it wholesale; **nothing recomputes it in the
background**, so a write that changes whether an entity is projected maintains
those links itself, and there is no job to run afterwards.

Links written straight through `linksManager` do not clear the parent's cached
copy the way `persistStructureLinks()` does, so those writes drop the affected
elements from the element cache themselves. See [category.md](category.md) and
[geo.md](geo.md) for the per-entity detail.
