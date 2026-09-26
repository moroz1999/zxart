# Entity deletion — implementation

Domain rules: [../domain/entity-deletion.md](../domain/entity-deletion.md).

Deleting a whole entity (picture, tune, prod, release, author, author alias,
group, group alias, party, press article) is available from that entity's edit
form only. There is no delete route and no delete page: the form header carries
the button, a confirmation dialog is the single step in between, and the SPA
navigates away once the element is gone.

## Backend

Every entity type has its own data endpoint — `/prod-data/`, `/release-data/`,
`/picture-data/`, `/tune-data/`, `/press-data/`, `/party-data/`,
`/author-data/`, `/author-alias-data/`, `/group-data/`, `/group-alias-data/` —
backed by its own `<Entity>DataService` in the entity's domain namespace.
`POST ?id=&action=delete` deletes the element and answers its `{id}`.

- An id of another entity type answers `404`: the endpoint names the type.
- The privilege is `publicDelete` on the element (`403` otherwise). `publicAdd`
  grants it to the creating user on the elements that support public creation,
  and a user linked to an author gets it for that author's works.
- The deletion is recorded in the actions log (`ActionsLogService`) before the
  element data is removed.
- Errors come as HTTP statuses with an `errorMessage` body.

The shared legacy `publicDelete` action serves only the legacy full-page
request: it redirects to the parent element, resolved before the deletion.

## Frontend

`ZxDeleteEntityButtonComponent` (`shared/ui/zx-delete-entity-button/`) is the
only entry point. It:

- asks `/element-privileges/` for `publicDelete` on the element and renders
  nothing when the privilege is missing, the user is anonymous, or the form is
  in creation/batch mode (no element id yet);
- opens a danger confirmation dialog through `ConfirmDialogService`;
- runs the `deleteRequest` the page supplies — the `delete` call of its entity's
  data API service (`ProdDataApiService.delete`, …, `shared/api/`) — and shows
  a failure dialog on an error status;
- navigates to the `redirectUrl` the page supplies.

Every edit page projects the button into the page header next to its `<h1>`
(`zxPageHeader`), so it sits in the top-right corner on the heading line. The
page supplies an entity-specific visible action label for the button and
confirmation dialog and owns the destination: collection routes for top-level entities
(`/pictures`, `/music`, `/prods`, `/authors`, `/groups`, `/parties`), the parent
entity for nested ones (a release returns to its prod, an author alias to its
author).
