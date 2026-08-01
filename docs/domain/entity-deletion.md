# Entity deletion

Deleting a whole entity (picture, tune, prod, release, author, author alias,
group, group alias, party, press article) is available from that entity's edit
form only. There is no delete route and no delete page: the form header carries
the button, a confirmation dialog is the single step in between, and the SPA
navigates away once the element is gone.

## Backend

The `publicDelete` action is registered on every element type whose form the SPA
serves. Its implementation is shared (`shared/action.publicDelete.class.php`) and
answers in two ways:

- on `/ajax/` (JSON renderer) it returns `{"success": true}` and leaves the
  destination to the client;
- on a legacy full-page request it redirects to the parent element, whose URL is
  resolved *before* the element is deleted.

The privilege is `publicDelete`, checked by `structureManager::performAction()`
like every other action. An unprivileged request never executes and therefore
never carries `success`, which the SPA reads as a failure. `publicAdd` grants the
privilege to the creating user on the elements that support public creation, and
a user linked to an author gets it for that author's works.

## Frontend

`ZxDeleteEntityButtonComponent` (`shared/ui/zx-delete-entity-button/`) is the
only entry point. It:

- asks `/element-privileges/` for `publicDelete` on the element and renders
  nothing when the privilege is missing, the user is anonymous, or the form is
  in creation/batch mode (no element id yet);
- opens a danger confirmation dialog through `ConfirmDialogService`;
- posts the action through `EntityDeleteApiService`
  (`shared/services/entity-delete-api.service.ts`), which resolves to `false`
  for anything other than `{"success": true}` and then shows a failure dialog;
- navigates to the `redirectUrl` the page supplies.

Every edit page projects the button into the page header next to its `<h1>`
(`zxPageHeader`), so it sits in the top-right corner on the heading line. The
page supplies an entity-specific visible action label for the button and
confirmation dialog and owns the destination: collection routes for top-level entities
(`/pictures`, `/music`, `/prods`, `/authors`, `/groups`, `/parties`), the parent
entity for nested ones (a release returns to its prod, an author alias to its
author).
