# zx-editing-controls

`zx-editing-controls` renders privilege-gated legacy action controls.

Use it for edit/delete/action controls that must call a legacy action URL after a click. These controls are actions, not navigation links: configure them without `href` so `zx-button` renders native `<button>` elements.

The component requests privileges through `ElementPrivilegesApiService` only after `CurrentUserService.isAuthenticated$` confirms that the user is authenticated. Anonymous users must not trigger privilege requests.

Feature components pass action configuration and an action URL builder. Keep entity-specific action names, labels, and confirmation text in the feature/domain layer.

Use the default `navigation="router"` for Angular routes. Set `navigation="document"` for legacy action URLs that must load a PHP form outside the SPA router.

While authenticated privilege data is loading, the component renders three skeleton ribs that match button controls.

## Confirmation and inline execution

An action may carry two optional descriptors:

- `confirm` — opens `ConfirmDialogService.confirm()` before the action runs; cancelling stops it. Only `messageKey` is required: the title and confirm-button labels fall back to the action label, the cancel label to `form.cancel`. Set `danger: true` for destructive actions.
- `run` — executes the legacy action through `FormSaveApiService` (`/ajax/`) instead of navigating to a confirmation page. With `targetPath` the component navigates to `/{targetPath}/{id}` of the element returned by the action; without it, the result is reported in a single-button dialog (`successKey` / `failureKey`), which is also used when the request fails.

An action that only needs a yes/no step before doing its work must combine `confirm` with `run` rather than routing to a dedicated confirmation page.
