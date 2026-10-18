# zx-toggle

Segmented control that picks one of a few options. Use it for a choice between
modes (a view layout, a theme, a text format), not for an on/off flag that
reads as a sentence — that is `zx-checkbox-field`.

## Inputs
- `options: ZxToggleOption[]` — `{value, label?, icon?}`; give every option a
  label or an icon.
- `size: 'xs' | 'sm' | 'md'` — button heights; `xs` is the compact variant for
  toolbars and dense rows, `sm` sits next to form inputs.
- `ariaLabel` — names the group for assistive technology when no visible label
  is linked to it.

## Binding
- As a form control: `formControlName` / `formControl`. Writing a value never
  reports a change; only a user's pick does. A disabled control locks the
  buttons.
- Outside a form: `[value]` and `(valueChange)`.

## Notes
- Native `<button>` elements are part of its semantic contract: each option is a
  pressed/unpressed button (`aria-pressed`) inside a `role="group"` container.
- In a form, place it in `zx-form-field` with a `zx-form-label`, inside
  `zx-form-control`, like any other input. The production form's text-format
  fields are the reference.
