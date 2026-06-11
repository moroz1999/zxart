в # zx-form

Form layout family adapted from the React form specification.

`ng-zxart/src/app/shared/ui/zx-form/`

## Component hierarchy

```txt
form[zxForm]
├── zx-form-field
│   ├── zx-form-label      (first child — label cell)
│   └── zx-form-control    (second child — control cell wrapper)
├── zx-form-fieldset
├── zx-form-section
└── zx-form-actions
```

```txt
zx-form-section
├── zx-form-field
└── zx-form-fieldset
```

`zx-form-fieldset` can be used directly inside the form. `zx-form-section` is only needed when a form has a large visual or logical section.

## `[zxForm]` directive

Applied to a native `<form>` element. Styles live in `zx-form.directive.scss` (loaded globally via `styles.scss`).

| Prop | Values | Default |
|---|---|---|
| `fieldsLayout` | `horizontal` \| `vertical` | `vertical` |
| `mobileFieldsLayout` | `horizontal` \| `vertical` | `vertical` |
| `sectionWrap` | boolean | `false` |
| `divided` | boolean | `false` |

- `horizontal` renders each `zx-form-field` as a two-column grid row: fixed-width label column + control column.
- `mobileFieldsLayout="vertical"` collapses horizontal rows to stacked label/control on mobile.
- `sectionWrap` lays `zx-form-section` children out as a flex-wrap row (side-by-side sections) and removes the form's default `max-width`. Sections collapse to a single column on narrow screens automatically via flex-basis.
- `divided` switches to the bordered appearance: fieldsets get their own padding (`--zx-form-fieldset-padding`) and are separated by divider lines, side-by-side sections get a vertical divider (horizontal on mobile), and `zx-form-actions` becomes a footer strip with `--zx-form-footer-bg`. Combine with `zx-panel padding="none"` so the form owns all panel padding.

## `zx-form-field`

One form row. Always two children: `zx-form-label` first, `zx-form-control` second.

## `zx-form-label`

Renders a native `<label>`. Typography comes from the `--zx-form-label-*` component variables (bold, primary text color — prominent like the design mockups).

| Prop | Values | Default |
|---|---|---|
| `for` | string (id of the control) | — |
| `required` | boolean | `false` |

## `zx-form-control`

Dumb layout wrapper for the control cell. No context, no binding — only the flex container the cell needs. Field hints (`appText="caption"`) go inside it, below the control.

## `zx-form-fieldset`

A field-like unit with one common `legend` and several related `zx-form-field` children. In horizontal layout it behaves like one form row: `[legend] [nested fields]`. The legend renders as a small uppercase group title (`--zx-form-legend-*` variables) with an optional icon.

| Prop | Values | Default |
|---|---|---|
| `legend` | string | `''` |
| `icon` | string (svg asset name, e.g. `search`) | `''` |

## `zx-form-section`

Use only when there is a real section title / visual block. Do not wrap fieldsets into sections by default. `title` is optional — omit it when sections are purely structural (e.g. a two-column layout without headings).

| Prop | Values | Default |
|---|---|---|
| `title` | string | `''` |

## `zx-form-actions`

Action row for `zx-button` elements.

| Prop | Values | Default |
|---|---|---|
| `align` | `start` \| `center` \| `end` \| `between` | `end` |

## Example

```html
<form zxForm fieldsLayout="horizontal" mobileFieldsLayout="vertical" (ngSubmit)="save()">
  <zx-form-field>
    <zx-form-label for="article-unit" [required]="true">{{ 'form.unit' | translate }}</zx-form-label>
    <zx-form-control>
      <zx-select id="article-unit" [options]="unitOptions" [(ngModel)]="unit" name="unit"></zx-select>
    </zx-form-control>
  </zx-form-field>

  <zx-form-fieldset [legend]="'form.default-storage' | translate">
    <zx-form-field>
      <zx-form-label for="storage-main-site">{{ 'form.main-site' | translate }}</zx-form-label>
      <zx-form-control>
        <zx-select id="storage-main-site" [options]="siteOptions" [(ngModel)]="mainSite" name="mainSite"></zx-select>
      </zx-form-control>
    </zx-form-field>
  </zx-form-fieldset>

  <zx-form-actions align="end">
    <zx-button color="secondary" type="button">{{ 'form.cancel' | translate }}</zx-button>
    <zx-button color="primary" type="submit">{{ 'form.save' | translate }}</zx-button>
  </zx-form-actions>
</form>
```
