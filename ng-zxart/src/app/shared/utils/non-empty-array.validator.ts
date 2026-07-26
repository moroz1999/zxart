import {AbstractControl, ValidationErrors} from '@angular/forms';

/**
 * Marks a multi-value control (entity pickers, category trees) as `required`
 * while it holds no items. Angular's own `required` treats an empty array as a
 * filled value, so list fields the backend depends on need this instead.
 */
export function nonEmptyArray(control: AbstractControl): ValidationErrors | null {
  return Array.isArray(control.value) && control.value.length > 0 ? null : {required: true};
}
