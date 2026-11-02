# zx-steps

Vertical list of the steps of a process, each with a round status marker beside its content.

`ng-zxart/src/app/shared/ui/zx-steps/`

## Components

- `zx-steps` — the list (`role="list"`), steps one under another.
- `zx-step` — one step (`role="listitem"`): the marker, then the projected content.

## zx-step props

| Prop | Values | Default |
|---|---|---|
| `number` | number \| `null` | `null` |
| `status` | `number` \| `done` \| `failed` \| `running` \| `pending` | `number` |

## Rules

**Status**: `number` shows the step's number for a step not yet run; `done` a check, `failed` an exclamation mark, `running` a spinner. `pending` shows the number and dims the whole step, for a step that waits for another.

The marker is decorative (`aria-hidden`): the content must say what the step is; the outcome of a step that failed is told in its content too.
