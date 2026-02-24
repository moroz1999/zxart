## CMS
The project is based on Trickster CMS, a proprietary CMS without documentation.
CMS is organized as a set of packages (cms, homepage, project). Project is a top-priority package, and its files override all others.
In future we will get rid of this unsupported CMS by incorporating its functionality into our project.

## File structure:
- /htdocs/ - mapped to public web root
- /ng-zxart/ - Angular frontend. This covers only a pair of views from the whole module yet. See angular.md for more info.
- /project/ - "zxart" itself; all domain-related source code, extends the CMS packages.
- /project/core/ - legacy services, models, helpers. Should be refactored.
- /project/core/ZxArt/ - modern services with namespaces and intended structure. All new code goes here except when dealing with legacy modules.
- /project/core/di-definitions.php - PHP-DI definitions for the project package.
- /project/css/public/ and /project/js/public/ - legacy public frontend assets, built on the fly. CSS files must follow naming conventions: `module.{name}.css` or `component.{name}.css`.
- /project/templates/ - legacy smarty templates. Follow [Design System](design-system.md) for subcomponents.
- /trickster-cms/ - CMS source code. Can be edited directly. In prod environment it is served from composer.
- /trickster-cms/cms/core/di-definitions.php - core PHP-DI definitions shared by all packages (SM factories, DB, languages, etc.).
- /trickster-cms/homepage/core/di-definitions.php - PHP-DI definitions for the homepage package.
- ./tests/ - phpunit tests. All new functionality should be covered by tests.
- Never access anything inside `temporary`. It contains cache files (e.g., template cache, bundle cache).

## Terminology
- **`controller`** — the bootstrap singleton (`controller.class.php`, `controller::getInstance()`). Initializes the request, builds the DI container, and dispatches to the application.
- **`controllerApplication`** — the request handler class (e.g. `publicApplication`, `adminApplication`). One per request, resolved from the DI container. Contains `initialize()` and `execute()` methods.

## PHP-DI Dependency Injection

Services are wired via PHP-DI. There is **one container per request**, built from the merged `di-definitions.php` files of all three packages (cms → homepage → project, with project having highest priority).

### DependencyInjectionContextTrait
Most CMS objects (controllers, structure elements, actions) use this trait, which provides:
- `getService(ClassName::class)` — checks `localServices` first, then falls through to the PHP-DI container.
- `setService($key, $object)` — stores an object in `localServices`, overriding the container for this context.
- `setLocalServices(array)` / `setContainer(Container)` — used internally to propagate DI context.
- `instantiateContext(DependencyInjectionContextInterface $obj)` — copies the current `localServices` and container to a child object (e.g. when SM creates a structure element).

**Always use class constants:** `$this->getService(MyService::class)`. String literals like `$this->getService('myService')` are legacy and must not be introduced.

### di-definitions.php conventions
- Entries with no special configuration do not need to be listed — PHP-DI autowires them by default.
- Only add explicit entries when a class requires constructor/method parameter overrides.
- Named string keys (e.g. `'publicStructureManager'`, `'db'`) are reserved for cases where a class cannot be a key (e.g. aliases or multiple instances of the same class).

## System Concepts

### Modules and Structure (Structure Elements)
CMS content and functionality are organized as a hierarchy of "Structure Elements".
- Each element has a type (e.g., `comment`, `zxProd`).
- Each `structureElement` contains `dateCreated` and `dateModified` properties. In most contexts, these properties contain formatted date strings. To get the original UTC timestamps (as integers), use the `$element->getCreatedTimestamp()` and `$element->getModifiedTimestamp()` methods.
- Element code is located in `{package}/modules/structureElements/{type}/`.
- Main class: `structure.class.php`.
- Definitions of available actions: `structure.actions.php`.
- For new elements that are not yet saved to the database, the `id` property is not `null`. It is a synthetic string in the format `id/{parentId}/action/{actionName}/`. To check if an element is already persisted in the database, use `$element->hasActualStructureInfo()`.
- Elements are linked to each other via links. The default link type is `structure`. 
- Hardcoding link types is strictly prohibited. Use the `ZxArt\LinkTypes` enum instead.
- When creating a new element using `structureManager::createElement()`, you can (and should, if it's not a standard parent-child relationship) specify a custom link type via the `$linkType` parameter. For example, comments are created using `LinkTypes::COMMENT_TARGET->value`.

### Action System
Actions on elements are implemented as separate classes in the module folder:
- File name format: `action.{actionName}.class.php`.
- Class name format: `{actionName}{ModuleName}`. For example, for the `comment` module and the `receive` action, the class name will be `receiveComment`.
- The class inherits from `structureElementAction`.
- The `execute()` method contains the main logic.
- The `setExpectedFields()` method defines which fields should be mapped from the request to the element. **Caution:** if a field is listed in `expectedFields` but is missing from the request (and not present in the form), it may overwrite existing data with null/empty values.
- The `setValidators()` method is responsible for validating incoming data.
- Public actions are available via URLs like `index.php?id={elementId}&action={actionName}`.

### Privileges
Privileges are managed through `privilegesManager`.
- Privileges can be linked to a user, an element, a module, and a specific action.
- Privileges are automatically checked before executing an action.
- In Smarty templates, global privileges are available in the `$privileges.{module}.{action}` array.
- For specific elements (like comments), privileges should be fetched via `$element->getPrivileges()`. Note that this returns the privileges array filtered for the element's structure type (e.g., `$privileges.actionName` instead of `$privileges.moduleName.actionName`).
- Example of programmatic privilege granting: `$privilegesManager->setPrivilege($userId, $elementId, 'module', 'action', 1)`. After programmatic changes, it is necessary to call `$user->refreshPrivileges()` (where `$user` is an instance of the `user` class) to clear session-cached privileges and internal `privilegesManager` caches. Using `$privilegesManager->resetPrivileges()` only clears the manager's cache but not the user session cache.

### View System (Templates)
- Smarty template engine is used. This is considered a **legacy view system**.
- Project templates are located in `project/templates/public/`.
- Template selection often depends on the element's `viewName` or is hardcoded in the controller/action.
- The `$element` variable is available in most views. It is an instance of the entity (Structure Element) to which the view refers.
- Components can be included via `{include file=$theme->template("name.tpl")}`.
- Translation strings are stored in the database (the `translations` service resolves them at runtime).
- Do NOT use the `style` attribute. Use full semantic class names instead. Styling should be handled in CSS files.

### Current User
- Do NOT inject `CurrentUser` via PHP-DI. It reads session storage during construction and must be created only after the controller starts the session.
- Use `CurrentUserService` as the injectable dependency instead.

### URL-based Action Handling
If you navigate to a URL like `$element->getUrl() . 'id:' . $element->id . '/action:actionName/'`, the CMS engine automatically resolves this:
1. It identifies the element by ID.
2. It checks if the user has privileges for the specified `actionName` on that element.
3. If authorized, it executes the corresponding action class.
This is the standard way to trigger actions from the frontend.

## Legacy Conventions
- Action classes for a module must follow the `{actionName}{ModuleName}` naming convention (e.g., `receiveComment`).
- Action files must be named `action.{actionName}.class.php` (e.g., `action.receive.class.php`).
- `getService('stringKey')` with a string literal is legacy. Always use `getService(ClassName::class)` instead.

## See also
- [Controllers and StructureManager](cms/controllers.md) — structureManager context (public/admin), getElementById modes, AJAX operations, error logging
