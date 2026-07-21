# Loading states

Use the standalone `zxLoadingState` directive when displayed content is being
refreshed. It dims and softly blurs the content, blocks interaction, exposes
`aria-busy`, and shows a spinner over it, all while preserving the current
content dimensions.

Initial loads use a concrete skeleton matching the target layout. Apply
`zxLoadingState` only to content that is already displayed during pagination or
filter-driven reloads. A list that swaps its items for a skeleton on every page
is wrong: the skeleton belongs to the first load, when there is nothing to dim
yet.

Page switching is the directive's main job. Every paginated list wraps its
results in `zxLoadingState` and passes the same flag to
[zx-pagination](zx-pagination.md), which only locks its own controls. Result
lists must not add a spinner of their own.

## Spinner

The spinner is not a child of the host: the blur filter would blur it too, and it
makes the host a containing block for fixed positioning. The directive attaches
it to a CDK overlay and centres it on the intersection of the host and the
viewport, following scroll and resize, so the busy marker sits on the part of the
list the user actually sees. While the host is off screen the spinner stays
hidden.

It fades in slowly and only after a delay, so page switches that resolve quickly
never flash it, and fades out fast once the content is back. The timings live in
the `--zx-loading-state-spinner-*` variables.

Two blocks loading at the same time give two spinners: mark the refreshed result
block, not its container, and keep filter panels on their own disabled styling.
