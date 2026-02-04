# Theme Switcher Implementation Plan

## Overview

Implement a light/dark theme switcher with:
- Angular UI (gear icon + slide-out panel)
- PHP REST API for user preferences
- Database persistence for logged-in users
- localStorage fallback for anonymous users

---

## Phase 1: Database

### Migration File
**File:** `db/migrations/2026.02.04 - user-preferences.sql`

```sql
CREATE TABLE engine_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('string', 'boolean', 'integer') NOT NULL DEFAULT 'string'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE engine_user_preference_values (
    user_id INT NOT NULL,
    preference_id INT NOT NULL,
    value VARCHAR(255) NOT NULL,
    PRIMARY KEY (user_id, preference_id),
    FOREIGN KEY (preference_id) REFERENCES engine_preferences(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO engine_preferences (code, type) VALUES ('theme', 'string');
```

---

## Phase 2: PHP Backend (TDD)

### 2.1 Domain Layer

**Directory:** `project/core/ZxArt/UserPreferences/`

| File | Description |
|------|-------------|
| `Domain/PreferenceCode.php` | Enum with allowed codes (THEME) |
| `Domain/ThemeValue.php` | Enum for theme values (LIGHT, DARK) |
| `Domain/Preference.php` | Entity: id, code, type |
| `Domain/UserPreferenceValue.php` | Entity: userId, preferenceId, value |
| `Domain/Exception/InvalidPreferenceCodeException.php` | Domain exception |
| `Domain/Exception/InvalidPreferenceValueException.php` | Domain exception |

### 2.2 Infrastructure Layer

| File | Description |
|------|-------------|
| `Repositories/PreferencesRepository.php` | Fetches preference definitions |
| `Repositories/UserPreferenceValuesRepository.php` | CRUD for user values |

### 2.3 Application Layer

| File | Description |
|------|-------------|
| `DefaultUserPreferencesProvider.php` | Returns default values (theme=light) |
| `UserPreferencesService.php` | Main service: get/set preferences |
| `Dto/PreferenceDto.php` | Internal DTO |
| `Dto/PreferenceListDto.php` | Collection of preferences |

### 2.4 Controller Layer

| File | Description |
|------|-------------|
| `Controllers/UserPreferences.php` | REST controller |
| `Rest/PreferenceRestDto.php` | REST output DTO |
| `Rest/PreferenceListRestDto.php` | REST collection DTO |

### API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/user-preferences/` | Get all preferences (merged with defaults) |
| PUT | `/user-preferences/` | Upsert single preference |

### Request/Response Format

**GET /user-preferences/**
```json
{
  "responseStatus": "success",
  "responseData": [
    {"code": "theme", "value": "light"}
  ]
}
```

**PUT /user-preferences/**
Request (form-urlencoded):
- `code=theme`
- `value=dark`

Response:
```json
{
  "responseStatus": "success",
  "responseData": [
    {"code": "theme", "value": "dark"}
  ]
}
```

### Test Files

| File | Tests |
|------|-------|
| `tests/UserPreferences/PreferencesRepositoryTest.php` | DB queries for definitions |
| `tests/UserPreferences/UserPreferenceValuesRepositoryTest.php` | CRUD operations |
| `tests/UserPreferences/UserPreferencesServiceTest.php` | Business logic, merging defaults |
| `tests/UserPreferences/UserPreferencesControllerTest.php` | HTTP layer |

---

## Phase 3: Angular Frontend

### 3.1 Feature Module

**Directory:** `ng-zxart/src/app/features/settings/`

| File | Description |
|------|-------------|
| `models/preference.dto.ts` | DTO interfaces |
| `services/user-preferences.service.ts` | HTTP client for API |
| `services/theme.service.ts` | Theme application logic |
| `components/settings-panel/` | Slide-out panel component |
| `components/settings-trigger/` | Fixed gear button |
| `components/theme-toggle/` | Theme toggle control |

### 3.2 Theme Service Logic

```typescript
class ThemeService {
  // On init:
  // 1. If user logged in: fetch from API
  // 2. If anonymous: read from localStorage
  // 3. Apply to <html> element

  // On change:
  // 1. Apply immediately to <html>
  // 2. If logged in: PUT to API, revert on failure
  // 3. If anonymous: save to localStorage
}
```

### 3.3 UI Components

**Settings Trigger:**
- Fixed position right side
- Gear icon (Material Icons)
- Opens panel on click

**Settings Panel:**
- Slide-in from right (Angular animations)
- Header: "Settings" (translated)
- Section: "Theme"
- Toggle: Light / Dark (mat-button-toggle)

### 3.4 Translations

Add to `en.json`, `ru.json`, `es.json`:
```json
{
  "settings.title": "Settings",
  "settings.theme": "Theme",
  "settings.theme.light": "Light",
  "settings.theme.dark": "Dark"
}
```

### 3.5 Integration

**Entry point:** `app.component.ts`
- Include `<app-settings-trigger>` in root template
- Initialize ThemeService on app bootstrap

---

## Phase 4: OpenAPI Spec

**File:** `api/user-preferences.yaml`

Document both endpoints with schemas.

**Update:** `api/api.yaml` to reference new spec.

---

## Implementation Order (TDD)

1. **Database migration** - Create tables
2. **Repository tests** - Write failing tests
3. **Repositories** - Implement to pass tests
4. **Service tests** - Write failing tests
5. **Service + DTOs** - Implement to pass tests
6. **Controller tests** - Write failing tests
7. **Controller** - Implement to pass tests
8. **Angular service** - API client
9. **Angular ThemeService** - Logic with localStorage
10. **Angular components** - UI
11. **OpenAPI spec** - Documentation

---

## File List Summary

### PHP (new files)
```
project/core/ZxArt/UserPreferences/
├── Domain/
│   ├── PreferenceCode.php
│   ├── ThemeValue.php
│   ├── Preference.php
│   ├── UserPreferenceValue.php
│   └── Exception/
│       ├── InvalidPreferenceCodeException.php
│       └── InvalidPreferenceValueException.php
├── Repositories/
│   ├── PreferencesRepository.php
│   └── UserPreferenceValuesRepository.php
├── DefaultUserPreferencesProvider.php
├── UserPreferencesService.php
├── Dto/
│   ├── PreferenceDto.php
│   └── PreferenceListDto.php
└── Rest/
    ├── PreferenceRestDto.php
    └── PreferenceListRestDto.php

project/core/ZxArt/Controllers/UserPreferences.php

tests/UserPreferences/
├── PreferencesRepositoryTest.php
├── UserPreferenceValuesRepositoryTest.php
├── UserPreferencesServiceTest.php
└── UserPreferencesControllerTest.php

db/migrations/2026.02.04 - user-preferences.sql
api/user-preferences.yaml
```

### Angular (new files)
```
ng-zxart/src/app/features/settings/
├── models/
│   └── preference.dto.ts
├── services/
│   ├── user-preferences.service.ts
│   └── theme.service.ts
└── components/
    ├── settings-panel/
    │   ├── settings-panel.component.ts
    │   ├── settings-panel.component.html
    │   └── settings-panel.component.scss
    ├── settings-trigger/
    │   ├── settings-trigger.component.ts
    │   ├── settings-trigger.component.html
    │   └── settings-trigger.component.scss
    └── theme-toggle/
        ├── theme-toggle.component.ts
        ├── theme-toggle.component.html
        └── theme-toggle.component.scss

ng-zxart/src/assets/i18n/en.json (update)
ng-zxart/src/assets/i18n/ru.json (update)
ng-zxart/src/assets/i18n/es.json (update)
```

---

## Notes

- Anonymous users: theme stored in `localStorage` key `zxart_theme`
- Theme classes: `light-mode`, `dark-mode` on `<html>`
- Default theme: `light`
- Validation: only `light` or `dark` accepted for theme preference
