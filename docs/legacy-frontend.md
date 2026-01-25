## Legacy templates
- Subcomponents should be stored in `project/templates/public/` with a descriptive name (e.g., `component.name.tpl` or `module.name.tpl`).
- When a component is used in multiple places, use `{include file=$theme->template("component.name.tpl")}`.

## Legacy CSS
Public legacy CSS files are located in `project/css/public/`. They are detected automatically by legacy bundler on refresh and built into bundle. No imports are required.

### CSS Naming Conventions
Legacy CSS files must follow these naming patterns:
- **Business Modules**: `module.{modulename}.css` (e.g., `module.comment.css`)
- **Components**: `component.{componentname}.css` (e.g., `component.username.css`)
These files are automatically detected and bundled by the CMS legacy asset manager.

### Buttons
- `.button`: Base class for all buttons.
- `.delete_button`: Red button used for deletion actions. Often used together with `.button`.
- `.button_green`: Green button, usually for "Save" or "Submit" actions.
- `.form_button`: Specific styling for buttons inside forms.

### Layout & Components
- `.comment_controls`: Container for edit/delete buttons in comments. Positioned absolutely at the top right of the `.comment` block.