# zx-multi-select-filter

Universal multi-select control with a search filter and a scrollable checkbox list.
Selected values appear as removable chips above the filter, with a running count below.

`ng-zxart/src/app/shared/ui/zx-multi-select-filter/`

Implements `ControlValueAccessor` over `string[]` (the selected option values), so it
binds with `formControlName` / `[(ngModel)]`.

## Inputs

| Prop | Type | Default |
|---|---|---|
| `options` | `ZxSelectOption[]` | `[]` |
| `groups` | `MultiSelectGroup[]` | `[]` |
| `searchPlaceholder` | string | `''` |

- Provide `options` for a single flat list (e.g. languages, release formats).
- Provide `groups` (`{label, options}[]`) for a grouped list with uppercase group
  headers (e.g. hardware by category). Use one or the other.
- `searchPlaceholder` is a translated string supplied by the host form.

The chip remove control uses native `<button>` markup, permitted for this atomic
design-system control (design-system rule 10).

## Example

```html
<zx-multi-select-filter
  formControlName="language"
  [options]="languageOptions"
  [searchPlaceholder]="'prod-form.filter-language' | translate"
></zx-multi-select-filter>

<zx-multi-select-filter
  formControlName="hardwareRequired"
  [groups]="hardwareGroups"
  [searchPlaceholder]="'release-form.filter-hardware' | translate"
></zx-multi-select-filter>
```
