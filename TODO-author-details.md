# Plan: Author Details Page (Angular)

## Context
Заменяем легаси страницу автора (`author.details.tpl`) новым Angular-компонентом `<zx-author-details>`.
Образец дизайна — `ZXArt Design System/ui_kits/website/AuthorPage.jsx` + `AuthorPageWorks.jsx`.
Паттерн организации — `features/release-details` (один core API + sub-компоненты).

---

## Scope

### В рамках этого PR
1. Бэкенд: два новых REST-эндпоинта
2. Фронтенд: feature `author-details` с корневым кастом-элементом и 5 суб-компонентами
3. Обновление `author.details.tpl` → `<zx-author-details>`

### Deferred (отдельные задачи)
- Collaborators section (требует сложного SQL по совместным работам)
- Feed колонка голосов (данных нет в legacy)
- Скелетон для автора (аналогично `zx-prod-details-skeleton`)

---

## Backend

### 1. `/author-details/` — core profile endpoint

**Новые файлы:**
- `project/core/ZxArt/Authors/Dto/AuthorCoreDto.php`
- `project/core/ZxArt/Authors/Rest/AuthorCoreRestDto.php` (ObjectMapper-mapped)
- `project/core/ZxArt/Authors/Services/AuthorDetailsService.php`
- `project/core/ZxArt/Controllers/AuthorDetails.php`

**Маршрут:** по аналогии с `ReleaseDetails` — зарегистрировать в routing-конфиге как `/author-details/`.

**Параметр:** `GET /author-details/?id={elementId}`

**Поля `AuthorCoreRestDto`:**
```
id, title (handle), realName, url,
avatar (string|null),
joined (ISO date),
siteUser (login string),
location: [{title, url}],
roles: ['artist'|'musician'|'coder'],
badges: ['VIP'|'Volunteer'],
groups: [{id, title, url, years}],
aliases: [{id, title, url}],
links: [{url, label, icon}],
tech: {palette, ayChip, ayChannels, ayClock, intFreq},
counters: {pictures, tunes, prods, comments},
ratings: {artist: float, musician: float},
breadcrumbs: [{title, url}],
tabs: {hasPictures, hasTunes, hasProds}
```

**Реализация `AuthorDetailsService`** — аналогична `ReleaseDetailsService`:
- `structureManager->getElementById($id)` → cast to `authorElement`
- Бросает `ProdDetailsException` (404 / 400) при невалидном id или отсутствии элемента
- Собирает данные из методов `authorElement`: `getTitle()`, `realName`, `getUserId()`, `aliases`, `groups`, `links`, `techSettings`, счётчики

### 2. `/author-prods/` — все продукции автора

**Новые файлы:**
- `project/core/ZxArt/Authors/Dto/AuthorProdDto.php`
- `project/core/ZxArt/Authors/Rest/AuthorProdRestDto.php`
- `project/core/ZxArt/Controllers/AuthorProds.php`

**Параметр:** `GET /author-prods/?id={elementId}`

**Поля `AuthorProdRestDto`:**
```
id, title, url, year,
thumbnailUrl (string|null),
category (string),
votes, votesAmount,
rolesInProd: string[],   ← роли именно этого автора
coAuthors: [{name, url}]
```

Возвращает все продукции без пагинации (как `/pictures/` + `/tunes/`), потому что клиент делает role-фильтр локально.

### 3. OpenAPI spec

`api/author-details.yaml` — описывает оба эндпоинта по шаблону `release-details.yaml`.

---

## Frontend Angular

### Структура feature

```
ng-zxart/src/app/features/author-details/
├── components/
│   ├── zx-author-details/          ← корневой custom element
│   ├── zx-author-header/           ← avatar + bio + badges + tech-specs
│   ├── zx-author-mini-dashboard/   ← 3-колонка топ работ
│   ├── zx-author-works/            ← табы Графика / Музыка / Софт
│   │   ├── zx-author-graphics-tab/
│   │   ├── zx-author-music-tab/
│   │   └── zx-author-software-tab/
│   └── zx-author-comments/         ← обёртка над CommentsListComponent
├── services/
│   ├── author-core-api.service.ts  ← GET /author-details/
│   └── author-prods-api.service.ts ← GET /author-prods/
└── models/
    ├── author-core.dto.ts
    └── author-prod.dto.ts
```

### Корневой компонент `ZxAuthorDetailsComponent`

Аналог `ZxReleaseDetailsComponent`:
```typescript
@Input() elementId = 0;
core$: Observable<AuthorCoreDto | null> = of(null);

ngOnInit() {
  this.core$ = this.api.getCore(+this.elementId).pipe(shareReplay(1));
}
```
Custom element: `'zx-author-details'` в `app.module.ts`.

Шаблон:
```html
<ng-container *ngIf="core$ | async as core; else skeleton">
  <zx-breadcrumbs [items]="core.breadcrumbs"/>
  <h1 [heading]="'h1'">{{ core.title }}</h1>
  <zx-author-header [core]="core"/>
  <zx-author-mini-dashboard [elementId]="core.id" [tabs]="core.tabs"/>
  <zx-author-works [elementId]="core.id" [tabs]="core.tabs"/>
  <zx-comments-list [element-id]="core.id"/>
</ng-container>
```

### Компоненты и что они переиспользуют

**`ZxAuthorHeaderComponent`**
- Входной: `@Input() core: AuthorCoreDto`
- Отображает: аватар-плейсхолдер (SVG-иконка person), имя, badge (VIP/Volunteer через `ZxBadgeComponent`), role-chips, bio, stats-sentence, groups, aliases (collapsed > 7), external links, collapsible tech-specs
- Реиспользует: `ZxBadgeComponent` (`shared/ui/zx-badge/`)

**`ZxAuthorMiniDashboardComponent`**
- Inputs: `elementId`, `tabs`
- Загружает: `AuthorPicturesService` + `AuthorTunesService` + `AuthorProdsApiService`
- `BehaviorSubject<sort>` → переключение топа по голосам/году/запускам (client-side)
- Реиспользует: `ZxPictureCardComponent`, `ZxProdBlockComponent`

**`ZxAuthorWorksComponent`** (контейнер табов)
- Реиспользует: `ZxTabsComponent` + `ZxTabComponent` + `ZxTabContentDirective`

**`ZxAuthorGraphicsTabComponent`**
- Загружает ВСЕ картины через `AuthorPicturesService` один раз
- Локальная фильтрация: format, party, search; сортировка; группировка по году
- Пагинация: PAGE_SIZE = 24, client-side
- Реиспользует: `ZxPictureCardComponent`, `ZxPaginationComponent`

**`ZxAuthorMusicTabComponent`**
- Загружает все тюны через `AuthorTunesService`
- Фильтр по chip-формату, search; группировка по году; пагинация PAGE_SIZE = 20

**`ZxAuthorSoftwareTabComponent`**
- Загружает все продукции через `AuthorProdsApiService`
- Role-filter chips; при фильтре — плоский список; без — группировка по году
- Пагинация PAGE_SIZE = 12; Реиспользует: `ZxProdBlockComponent`

### Регистрация и template

В `app.module.ts`: `'zx-author-details': ZxAuthorDetailsComponent`

`project/templates/public/author.details.tpl`:
```smarty
<zx-author-details element-id="{$element->id}"></zx-author-details>
```

---

## Переиспользуемые сервисы (не трогаем)

| Сервис | Путь |
|---|---|
| `AuthorPicturesService` | `features/author-pictures/services/` |
| `AuthorTunesService` | `features/author-tunes/services/` |
| `CommentsListComponent` | `features/comments/components/comments-list/` |
| `RatingsListComponent` | `features/ratings/components/ratings-list/` |

---

## Порядок реализации

- [x] **1. Backend `/author-details/`** — `AuthorCoreDto`, `AuthorCoreRestDto`, `AuthorDetailsService`, `AuthorDetails` контроллер, маршрут
- [x] **2. Backend `/author-prods/`** — `AuthorProdDto`, `AuthorProdRestDto`, `AuthorProds` контроллер, маршрут
- [x] **3. OpenAPI spec** `api/author-details.yaml` (оба эндпоинта)
- [x] **4. Angular models** — `author-core.dto.ts`, `author-prod.dto.ts`
- [x] **5. Angular services** — `author-core-api.service.ts`, `author-prods-api.service.ts`
- [x] **6. `zx-author-details`** — корневой компонент + регистрация в `app.module.ts`
- [x] **7. `zx-author-header`** — avatar placeholder, bio, badges, role chips, groups, aliases, tech-specs
- [x] **8. `zx-author-mini-dashboard`** — 3-колонка топ работ (top 4 by votes per category)
- [x] **9. `zx-author-graphics-tab`** — year-группировка + пагинация
- [x] **10. `zx-author-music-tab`** — year-группировка + пагинация
- [x] **11. `zx-author-software-tab`** — role-filter + year-группировка + пагинация
- [x] **12. `zx-author-works`** — контейнер табов (собирает 9–11)
- [x] **13. Обновить `author.details.tpl`** → `<zx-author-details element-id=...>`
- [x] **14. Build** — без ошибок TypeScript/Angular (budget warnings только)

---

## Верификация

1. `docker compose run --rm node run build:docker` — без ошибок
2. Открыть страницу автора — рендерится Angular-компонент вместо легаси
3. Header: имя, роли, bio, группы, aliases collapse/expand, tech-specs toggle
4. Mini-dashboard: все три колонки, переключение sort
5. Works navigator: переключение табов, фильтры, поиск, пагинация
6. `<zx-comments-list>` отображается внизу
7. Страница работает в dark mode
8. `composer psalm` — без новых ошибок
