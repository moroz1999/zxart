# Picture Tags Quick Form Angular Migration

## Goal

Replace the legacy `tags.form.tpl` quick tags editor with Angular for all current quick-form usages:

- `zxPicture.details.tpl`
- `zxMusic.details.tpl`
- `zxProd.details.tpl`

The full create/edit forms for pictures, music, and prods are out of scope for this step.

## Scope

This task covers only the quick tags form rendered on detail pages.

It must:

- work for `zxPicture`, `zxMusic`, and `zxProd`
- load form data lazily only after the block is scrolled into view
- show a skeleton before lazy data is loaded
- render selected tags as removable UI items, not as plain text inside a single input
- replace the legacy quick-form JavaScript
- use new backend controllers and Angular services instead of the legacy form POST contract

It must not:

- change full edit forms such as `zxPicture.form.tpl`, `zxMusic.form.tpl`, `zxProd.form.tpl`
- reuse lazy loading inside full edit forms
- rely on Smarty-provided tags data for the quick form

## Architectural Split

The Angular solution must be split into two parts.

### 1. Tags Editor

Reusable editor component responsible for:

- rendering selected tags
- rendering suggested tags
- editing the current search input
- adding tags
- removing tags

The editor must not own lazy loading.

The editor must not request data from services.

The editor must not save data through services.

The editor is a pure UI component that receives data via inputs and reports user actions via outputs.

The editor UI state must be based on a structured tags collection, not on raw comma-separated text.

The editor must be designed so it can later be embedded into full edit forms where tags data will come from another endpoint and will be injected directly.

### 2. Quick Form Loader

Quick-form-only wrapper responsible for:

- lazy loading when the block enters the viewport
- showing a skeleton before data is fetched
- requesting tags data from the backend
- requesting required privileges from the backend
- saving updated tags
- showing save error state
- passing loaded data into the editor

This wrapper owns all service interaction for the quick-form scenario.

This wrapper is used only on detail pages in the quick-form scenario.

## Angular Structure

Create a dedicated Angular feature for the quick form, with a reusable editor inside it.

Suggested structure:

```text
ng-zxart/src/app/features/tags-quick-form/
  components/
    tags-quick-form/
  models/
  services/

ng-zxart/src/app/shared/lib/tags-quick-form-editor/

ng-zxart/src/app/shared/ui/zx-tags-input/
```

Recommended responsibilities:

- `zx-tags-quick-form`
  quick-form host custom element for legacy detail pages; owns loading and saving
- `zx-tags-quick-form-editor`
  reusable presentational editor component without lazy loading or persistence assumptions
- `zx-tags-input`
  shared UI component that renders selected tags as removable items with an input area for adding more

`zx-tags-quick-form` is registered as a custom element and embedded into Smarty templates.

`tags-quick-form-editor` must live in `shared/lib` because it is intended for reuse outside the quick-form feature.

The editor API must be suitable for reuse in full forms later.

`zx-tags-input` must live in `shared/ui` because this interaction pattern does not exist in the project yet and must not rely on Material UI.

## Backend Design

### Tags Controller

Add a new controller dedicated to reading and saving tags data.

Responsibilities:

- read current tags
- read suggested tags
- save updated tags

The service behind it must rely on existing entity functionality instead of duplicating tag logic.

Expected entity methods to reuse:

- `getTagsList()`
- `getSuggestedTags()`
- `updateTagsInfo()`
- `persistElementData()`

The REST API must expose structured tags data only.

`tagsText` is legacy and must not be used as the external JSON contract for this feature.

If some legacy entity behavior still requires text normalization internally, that conversion must stay inside the backend service and must not leak into controller DTOs or Angular models.

The controller must support all current quick-form entity types:

- `zxPicture`
- `zxMusic`
- `zxProd`

The controller must resolve the entity by global `structure_elements.id` and reject unsupported types.

### Element Privileges Controller

Add a separate generic controller for privilege checks.

Responsibilities:

- accept an element id
- accept a list of privilege names to check
- return a boolean map for the requested privilege names

This controller is intentionally generic and must not be coupled to tags logic.

Initial quick-form usage needs at least:

- `submitTags`

### DTOs and Services

Both controllers must follow the current REST pattern:

- internal service DTOs
- REST DTOs
- controller-level mapping through `ObjectMapper`

Required backend layers:

- controller
- service
- internal DTOs
- REST DTOs

## API Shape

Exact route names can be finalized during implementation, but the contract should follow this shape.

### Tags Read

```text
GET /tags/?id={elementId}
```

Returns:

- `elementId`
- `tags`
- `suggestedTags`

Current tag item fields:

- `id`
- `title`

Suggested tag item fields:

- `title`
- `description`

### Tags Save

```text
POST /tags/?id={elementId}
```

Request body:

- `tags`

Tag item fields:

- `id` when an existing tag is selected
- `title` for the tag label

Returns updated tags data:

- `elementId`
- `tags`
- `suggestedTags`

### Privileges Read

```text
GET /element-privileges/?id={elementId}&privileges=submitTags,otherPrivilege
```

Returns:

```json
{
  "submitTags": true
}
```

## Quick Form Rendering Flow

1. Smarty renders only the Angular host element with `elementId`.
2. Angular host requests required privileges immediately.
3. If `submitTags` is denied, the host renders nothing and leaves no visual trace.
4. If `submitTags` is allowed, the host shows a skeleton placeholder.
5. The host waits until the block enters the viewport.
6. After entering the viewport, the host requests tags data.
7. The editor is rendered with loaded data.

The lazy loading rule applies only to the quick form.

## Full Form Compatibility

This step must preserve a clean path for the next phase:

- full forms will not use the quick-form lazy wrapper
- full forms will reuse the editor component
- full forms will receive tags data from their own broader form endpoint
- full forms will inject that data directly into the editor component

Because of that, the editor API must not be coupled to viewport loading or detail-page privilege fetching.

## Legacy Template Integration

Replace the existing `tags.form.tpl` include output with the Angular custom element in:

- `project/templates/public/zxPicture.details.tpl`
- `project/templates/public/zxMusic.details.tpl`
- `project/templates/public/zxProd.details.tpl`

The legacy full forms remain unchanged.

After all three quick-form usages are migrated, remove the obsolete legacy quick-form JavaScript if no other code depends on it:

- `project/js/public/component.pictureTagsForm.js`

Also remove its registration from the theme asset list if it becomes unused.

## UX Requirements

The Angular quick form must preserve current user-visible behavior:

- selected tags rendered near the input as removable items
- suggestions list
- clicking a suggestion adds a tag and removes that suggestion from the local list
- each selected tag can be removed by clicking an `X` control
- autocomplete based on the current input query, not on parsing a comma-separated field

Additional requirements for the Angular version:

- skeleton before lazy data load
- loading state during save
- backend error display on failed save

## Component Contract

### Tags Editor

The editor component is UI-only.

It receives:

- selected tags
- suggested tags
- loading or disabled state if needed by the parent
- error text if needed by the parent

It emits:

- selected tags changes
- save intent

It does not:

- call backend services
- load tags data
- check privileges
- persist tags

### Quick Form Loader

The quick-form loader is the smart container.

It:

- loads privileges before rendering any visible quick-form UI
- renders nothing when required privileges are missing
- waits for viewport entry
- loads tags data
- decides whether the editor should be shown
- passes data into the editor
- listens to editor events
- performs save requests
- maps backend responses back into editor inputs

## Shared UI Requirement

The selected-tags interaction must be implemented as a new shared UI component.

Requirements for this component:

- lives in `ng-zxart/src/app/shared/ui/`
- does not use Material UI
- displays selected tags as removable UI items
- provides an input area for adding/searching tags
- is generic enough to be reused outside the quick-form feature

The quick-form editor should compose this shared UI component rather than implement the selected-tags UI ad hoc inside the feature.

## Data Contract Rule

For this feature, tags are represented as JSON lists end-to-end.

This applies to:

- backend REST DTOs
- Angular feature models
- editor inputs and outputs
- save payloads

`tagsText` is not part of the intended public contract.

## Verification

Implementation must be verified for:

- picture quick form
- music quick form
- prod quick form
- privilege denied case
- lazy load trigger on scroll
- skeleton shown before lazy load
- updated tags displayed after save

Required technical verification:

- OpenAPI files updated
- PHP tests
- Psalm
- Angular build
