# zx-user

Displays a user name with status icons (badges). Uses "icon name" layout.

`ng-zxart/src/app/shared/ui/zx-user/`

## Inputs

| Input | Type | Default | Description |
|---|---|---|---|
| `user` | `CommentAuthorDto` | required | User data (name, url, badges) |
| `linkDisabled` | `boolean` | `false` | When true, always renders name as `<span>` even if `user.url` is set |
| `namePrimary` | `boolean` | `false` | When true, renders name in primary text color (`--text-color`) instead of caption muted color |

## Theming

| Variable | Default | Description |
|---|---|---|
| `--zx-user-name-color` | `var(--zx-caption-color)` | Color of the name in span (non-link) mode. Controlled internally via `namePrimary` input — do not override from parent. |
