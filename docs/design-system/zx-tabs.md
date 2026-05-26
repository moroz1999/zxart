# zx-tabs

Tab navigation container with a single active projected content template.

`ng-zxart/src/app/shared/ui/zx-tabs/`

## Native Trigger Contract

`zx-tabs` owns its tab triggers and renders native elements internally:

- `<a>` is used when a tab has an `href`, preserving link semantics and open-in-new-tab behavior.
- `<button>` is used when a tab changes local content only.

This is an atomic component exception to the general `zx-button` consumption rule. Feature and page components must continue using `zx-button` for their own controls.
