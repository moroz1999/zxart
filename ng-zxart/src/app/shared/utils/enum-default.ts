import {EnumOption} from '../models/form-data-response';

/**
 * Initial value of a backend-driven enum select. An entity that has no value
 * stored yet starts on the first option, so the select shows exactly what the
 * form submits. Lists where "nothing" is a valid choice carry an empty first
 * option (`emptyBlank`, `emptyLabelKey`) and keep the empty value.
 */
export function enumDefaultValue(options: EnumOption[] | undefined, value: string): string {
  if (value !== '') {
    return value;
  }
  return options?.[0]?.value ?? '';
}
