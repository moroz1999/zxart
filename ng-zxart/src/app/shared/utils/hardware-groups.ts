import {TranslateService} from '@ngx-translate/core';
import {EnumOption} from '../models/form-data-response';
import {MultiSelectGroup} from '../ui/zx-multi-select-filter/zx-multi-select-filter.models';

/**
 * Turns the backend's hardware enum options into the grouped shape
 * `zx-multi-select-filter` renders.
 *
 * Option labels arrive from the editable catalog already localized; only the
 * category heading is a code the SPA owns (`hardware-group.<code>`). Callers
 * rebuild on `onLangChange`, because that heading is the part that changes.
 *
 * Shared by every form that picks hardware — release, production and batch
 * upload — so the grouping rule lives in one place.
 */
export function buildHardwareGroups(options: EnumOption[], translate: TranslateService): MultiSelectGroup[] {
  const byGroup = new Map<string, MultiSelectGroup>();

  for (const option of options) {
    const groupCode = option.group ?? '';
    let group = byGroup.get(groupCode);
    if (!group) {
      group = {
        label: groupCode === '' ? '' : translate.instant(`hardware-group.${groupCode}`),
        options: [],
      };
      byGroup.set(groupCode, group);
    }
    group.options.push({value: option.value, label: option.label});
  }

  // the backend already emits catalog order, so no sorting here
  return [...byGroup.values()];
}
