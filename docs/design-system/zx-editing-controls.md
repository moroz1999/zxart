# zx-editing-controls

`zx-editing-controls` renders privilege-gated legacy action controls.

Use it for edit/delete/action controls that must call a legacy action URL after a click. These controls are actions, not navigation links: configure them without `href` so `zx-button` renders native `<button>` elements.

The component requests privileges through `ElementPrivilegesApiService` only after `CurrentUserService.isAuthenticated$` confirms that the user is authenticated. Anonymous users must not trigger privilege requests.

Feature components pass action configuration and an action URL builder. Keep entity-specific action names, labels, and confirmation text in the feature/domain layer.
