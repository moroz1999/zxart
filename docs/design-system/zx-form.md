в # zx-form

Form layout family adapted from the React form specification.

`ng-zxart/src/app/shared/ui/zx-form/`

## Component hierarchy

```txt
form[zxForm]
├── zx-form-field
│   ├── zx-form-label      (first child — label cell)
│   └── zx-form-control    (second child — control cell wrapper)
│       ├── <control>          (zx-input / zx-textarea / zx-select …)
│       └── zx-control-errors  (per-control validation message)
├── zx-form-fieldset
├── zx-form-section
├── zx-form-message       (form-wide status message)
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

Dumb layout wrapper for the control cell. No context, no binding — only the flex container the cell needs. Field hints (`appText="caption"`) and `zx-control-errors` go inside it, below the control; the control's gap provides the spacing.

## `zx-control-errors`

Per-control validation message. Bind it to the field's `AbstractControl`; it reads the control's first
matching validator error, maps it to a translation key and shows it once the control is touched/dirty.
Reactive to the control via `AbstractControl.events`, so it works under OnPush. Place it inside
`zx-form-control`, right after the control. Form controls themselves render no validation text — this is
the single solution for all of them.

| Prop | Values | Default |
|---|---|---|
| `control` | `AbstractControl \| null` (required) | — |
| `messages` | `Record<validatorErrorKey, i18nKey>` | `{}` |
| `showOn` | `touched` \| `dirty` \| `always` | `touched` |

```html
<zx-form-control>
  <zx-input id="email" formControlName="email" type="email"></zx-input>
  <zx-control-errors
    [control]="form.controls['email']"
    [messages]="{required: 'feedback.error-email', email: 'feedback.error-email-format'}"
  ></zx-control-errors>
</zx-form-control>
```

Use a component property for the `messages` map (not an inline object literal) so its reference is stable
across change detection.

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

## `zx-form-message`

A status message that concerns the **whole form**, not a single field — e.g. the result of a submit
(success confirmation or a server/submission error). Place it as a direct child of the form (typically at
the top, or just above `zx-form-actions`). For per-control validation messages use the control-level
solution instead, not this component.

Spacing is owned by the surrounding layout (`zx-stack` / the form), so the component carries no margins.
Accessibility roles are set automatically: `alert` for `error`, `status` for `success`.

| Prop | Values | Default |
|---|---|---|
| `variant` | `error` \| `success` | `error` |

```html
<zx-form-message *ngIf="submitError" variant="error">{{ submitError | translate }}</zx-form-message>
<zx-form-message *ngIf="submitted" variant="success">{{ 'form.sent' | translate }}</zx-form-message>
```

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
