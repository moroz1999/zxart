## CMS
The project is based on Trickster CMS, a proprietary CMS without documentation.
CMS is organized as a set of packages (cms, homepage, project). Project is a top-priority package, and its files override all others.
In future we will get rid of this unsupported CMS by incorporating its functionality into our project.

## File structure:
- /htdocs/ - mapped to public web root
- /ng-zxart/ - Angular frontend. This convers only a pair of views from whole module yet. see angular.md for more info
- /project/ - project is "zxart" itself, this folder contains all domain-related source code and extends structure of CMS package.
- /project/core/ - legacy services, models, helpers. This should be refactored.
- /project/core/ZxArt/ - modern services with namespaces and intended structure. All new code goes here except when dealing with legacy modules.
- /project/css/public/ and /project/js/public/ - legacy public frontend assets. they are built on the fly. CSS files must follow naming conventions: `module.{name}.css` or `component.{name}.css`.
- /project/services/ - legacy DI container services. should not be added, only refactored to PHP-DI.
- /project/templates/ - legacy smarty templates. Follow [Design System](design-system.md) for subcomponents.
- /trickster-cms/ - copy of CMS. In dev environment project is linked to this folder. In prod environment it is served from composer.
- ./tests/ - phpunit tests. all new functionality should be covered by tests.

## System Concepts

### Modules and Structure (Structure Elements)
CMS content and functionality are organized as a hierarchy of "Structure Elements".
- Each element has a type (e.g., `comment`, `zxProd`).
- Each `structureElement` contains `dateCreated` and `dateModified` properties. In most contexts, these properties contain formatted date strings. To get the original UTC timestamps (as integers), use the `$element->getCreatedTimestamp()` and `$element->getModifiedTimestamp()` methods.
- Element code is located in `{package}/modules/structureElements/{type}/`.
- Main class: `structure.class.php`.
- Definitions of available actions: `structure.actions.php`.
- For new elements that are not yet saved to the database, the `id` property is not `null`. It is a synthetic string in the format `id/{parentId}/action/{actionName}/`. To check if an element is already persisted in the database, use `$element->hasActualStructureInfo()`.
- Elements are linked to each other via links. The default link type is `structure`. When creating a new element using `structureManager::createElement()`, you can (and should, if it's not a standard parent-child relationship) specify a custom link type via the `$linkType` parameter. For example, comments are linked to their targets using the `commentTarget` link type.

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
- Do NOT use the `style` attribute. Use full semantic class names instead. Styling should be handled in CSS files.

### URL-based Action Handling
If you navigate to a URL like `$element->getUrl() . 'id:' . $element->id . '/action:actionName/'`, the CMS engine automatically resolves this:
1. It identifies the element by ID.
2. It checks if the user has privileges for the specified `actionName` on that element.
3. If authorized, it executes the corresponding action class.
This is the standard way to trigger actions from the frontend.

## Legacy Conventions
- Action classes for a module must follow the `{actionName}{ModuleName}` naming convention (e.g., `receiveComment`).
- Action files must be named `action.{actionName}.class.php` (e.g., `action.receive.class.php`).

## Documentation Standards
- All documentation and comments must be in English.
- Documentation updates must be placed in the appropriate .md file (e.g., PHP rules in php.md, general rules in rules.md, architecture details in architecture.md).