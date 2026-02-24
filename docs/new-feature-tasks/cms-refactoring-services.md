В движке есть сервисы и регистр сервисов. Это по сути легаси контейнер для сервисов, который позволяет регистрировать и получать сервисы по их идентификаторам.
Ещё в движке есть PHP-DI.
Есть три пакета project, trickster-cms/cms и trickster-cms/homepage.
В каждом из них есть core/di-definitions.php для PHP-DI.
В каждом из них есть services, где лежат легаси сервисы.
Глобальная задача - перенести сервисы в PHP-DI.

## Задачи

### ✅ 1. Перенести сервисы в PHP-DI
Для каждого легаси сервиса завести в PHP-DI аналогичный скрипт запуска, не потеряв ВООБЩЕ ничего из его конфигурации.

### ✅ 2. Заменить строковые вызовы getService на классовые
Записи `$this->getService('myService')` переделать на `$this->getService(MyService::class)` и добавить use.

### ✅ 3. Удалить все сервисы из legacy контейнера
Цель - удалить все сервисы из legacy контейнера. Папки services в итоге должны остаться пустыми.

### ✅ 4. Убрать вызовы legacy контейнера из di-definitions
Некоторые легаси сервисы были перенесены в di-definitions.php наполовину - там вызывался старый контейнер. Переделано капитально, без вызова легаси.

### ✅ 5. Удалить старый контейнер (DependencyInjectionServicesRegistry)
Убрать сам старый контейнер и его использование из DependencyInjectionContext. Теперь там только PHP-DI.
- Удалён `DependencyInjectionServicesRegistry.class.php`
- Удалены `getRegistry()` и `private $registry` из `controller.class.php`
- `persistableCollection` и `pdoTransport` переведены на прямой доступ к контейнеру
- Все оставшиеся строковые `getService('ClassName')` заменены на `getService(ClassName::class)`
- Добавлен алиас `'db' => DI\get(Connection::class)`

### ✅ 6. Архитектура structureManager через PHP-DI
Реализована система, где тип SM (public/admin) определяется контроллером через PHP-DI, а не через код в `execute()`.

**Три фабрики SM в `trickster-cms/cms/core/di-definitions.php`:**
- `structureManager::class` — **public SM по умолчанию** (rootMarkerPublic)
- `'publicStructureManager'` — независимая фабрика public SM (для явного использования в сложных приложениях)
- `'adminStructureManager'` — admin SM (rootMarkerAdmin) + **побочный эффект**: `$container->set(structureManager::class, $sm)` — переопределяет контейнерный кэш

**Инжекция в admin-приложения через di-definitions:**
```php
adminApplication::class => autowire()
    ->method('setService', 'structureManager', DI\get('adminStructureManager')),
```
Это вызывает фабрику admin SM (и её побочный эффект) до выполнения `execute()`, и кладёт admin SM в `localServices['structureManager']`.

**Сложные приложения (ручное управление SM):**
- `ajaxSearch.class.php`, `api.class.php` — явно получают `'adminStructureManager'` или `'publicStructureManager'` в зависимости от режима

**Удалена инициализация SM из `execute()` всех контроллеров** — вместо этого SM приходит через DI.
