# Theme Switcher – Implementation Instructions

## Goal

Implement a light / dark theme switcher.

- UI: right-side fixed gear tail + slide-out settings panel
- Control: single toggle (dark / light)
- Theme applied via:
    - `<html class="light-mode">`
    - `<html class="dark-mode">`
- Backend persistence per logged-in user

---

## Frontend (Angular)

### UI Structure

- Fixed element on the right side of the screen
    - Contains a gear button
- Clicking the gear:
    - Opens a panel sliding from the right
- Panel contains:
    - Section "Theme"
    - Toggle control (Light / Dark)

### Behavior

1. On application start:
    - Call `GET /user-preferences`
    - Read preference with `code = theme`
    - Apply theme class to `<html>`

2. On toggle change:
    - Immediately update `<html>` class
    - Send `PUT /user-preferences` with:
      ```json
      {
        "code": "theme",
        "value": "dark" | "light"
      }
      ```
    - If request fails:
        - Revert to last known server state

### Theme Application Rules

- Always remove both classes first:
    - `light-mode`
    - `dark-mode`
- Then add the active one

---

## Backend (PHP)

### Endpoints

- `GET /user-preferences`
    - Returns all preferences for current user (merged with defaults)

- `PUT /user-preferences`
    - Upserts a single preference for current user
    - Returns full merged preference list

---

## Database

### Tables

#### engine_preferences

| Column       | Description                     |
|-------------|---------------------------------|
| id          | PK                              |
| code        | Preference code (e.g. theme)    |
| type        | Preference type (string, etc.)  |

#### engine_user_preference_values

| Column         | Description                          |
|---------------|--------------------------------------|
| user_id       | Logged-in user id                    |
| preference_id | FK to engine_preferences.id          |
| value         | Stored preference value              |

Constraints:
- Unique (`user_id`, `preference_id`)

---

## Backend Architecture

### Layers

1. Controller
2. Application (services)
3. Domain
4. Infrastructure (repositories)

Each layer:
- Has its own DTOs
- Has its own exceptions
- Does not leak implementation details

---

## Default Preferences

- Stored in code (not DB)
- Provided by `DefaultUserPreferencesProvider`
- Initial values:
    - `theme = light`

---

## Validation Rules

- Only known preference codes allowed
- For `theme`, only:
    - `light`
    - `dark`

Validation lives in:
- Domain / Application layer
- Not in controllers

---

## TDD Requirements

Implementation order:

1. Repository tests
2. Service tests
3. Controller tests
4. Implementation

No production code without a failing test first.

---

## Completion Criteria

- Theme switch works without reload
- Preference persists across reloads
- New user gets default theme
- DB contains one preference definition and per-user values
- Code follows existing project conventions

