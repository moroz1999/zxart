# Users and user groups — implementation

Domain rules: [../domain/users.md](../domain/users.md).

The management screens live in the `/manage` section ([manage-section.md](manage-section.md)).

## Storage

- A user is a `user` element (`module_user`) in the `users` folder, a group a
  `userGroup` element (`module_user_group`) in the `userGroups` folder. Both
  folders are under the **admin** root.
- Membership is a `userRelation` link from the group to the user
  (`LinkTypes::USER_RELATION`).
- The registration date is the element's `dateCreated`.

## Privileges

Held on the public root (`UserManagementPrivileges`), granted to `user managers`
by `db/migrations/2026.11.05 - user management privileges.sql`:

| Privilege | Opens |
|---|---|
| `root.manageUsers` | `/users-data/` (list, details, `update`), `/users-stats-data/`, reading `/user-groups-data/` |
| `user.ban` | `/users-data/` `ban` |
| `userGroup.receive` | `/user-groups-data/` reading, `create`, `update` |
| `userGroup.delete` | `/user-groups-data/` `delete` |
| `userGroup.showForm`, `userGroups.showFullList` | none — the public structure manager makes a group element only for a user holding its default action, and the groups folder only with `showFullList` |

`root.manageUsers` is a privilege of its own because every visitor holds
`user.receive` and `users.showFullList` on the public root (profile editing).

Users and groups sit under the admin root, so `UserRepository` and
`UserGroupRepository` load them by id straight to the root
(`getElementById($id, null, true)`), not through the public path.

## Backend (`ZxArt\Users`)

| Piece | Job |
|---|---|
| `UsersListDbal` | The list: search (`like` with escaped wildcards) on `userName`/`email`, group (`userRelation` subquery), status, sort, page, count |
| `UserGroupsDbal` | Groups by name, member counts, groups of users, `replaceGroupsOfUser()` |
| `UsersStatsDbal` | Totals and registrations per `Y-m` month |
| `UserActivityDbal` | Comments and votes of a user |
| `UserAccountsDbal` | Whether a name or an email is taken |
| `UsersListService`, `UsersStatsService`, `UserGroupsService`, `UserDetailsService` | Reads |
| `UpdateUserHandler` | Fields of `UserFieldsDto` (`null` keeps a field), the group list replaces the groups; refuses a blank or taken name, a taken email, a group id that is no group (`400`). Logs `manageUsers` |
| `BanUserHandler` | `userElement::ban()` — the admin panel's ban: deletes comments and `votes_history` rows, sets `banned`. Logs `ban` |
| `Create/Update/DeleteUserGroupHandler` | Name (required) and description; the structure name follows the name (`UserGroupFieldsWriter`). Log `receive` / `delete` |

Controllers: `UsersData`, `UsersStatsData`, `UserGroupsData`; specs in
`api/users-data.yaml` and `api/user-groups-data.yaml`.

Tests: `tests/Integration/Users/`.

## Frontend

- `features/manage-users/`: DTOs, `users-list-query.ts` (the list query ↔ URL
  params ↔ request params, column sorting), `ManageUsersApiService` (list,
  details, update, ban, stats — no cache: every page is its own request),
  `ManageUserGroupsApiService` (a `BehaviorSubject` store replaced from every
  write; `refresh()` after a user's groups change, so the counts follow).
- Pages:
  - `/manage/user-stats` — tiles and `zx-stats-bar-chart`;
  - `/manage/users` — the list. Filters live in the query params (`search`,
    `group`, `status`, `sort`, `direction`, `page`; defaults are left out); the
    search navigates 300 ms after the last keystroke;
  - `/manage/users/:id` — facts, form, group checkboxes, ban;
  - `/manage/user-groups`, `/manage/user-groups/add`, `/manage/user-groups/:id`.
