import {
  PRIVILEGE_CATEGORY_SAVE,
  PRIVILEGE_COUNTRY_SAVE,
  ROOT_PRIVILEGE_EDIT_HARDWARE,
} from '../../shared/services/root-privilege.service';

export interface ManageSection {
  href: string;
  labelKey: string;
  privilege: string;
}

/**
 * The sections of the management screen, in the order they are offered.
 *
 * A section is offered to whoever may **save** in it; what may be deleted is a
 * separate privilege the screens ask for on their own. The tabs and the
 * `/manage` entry point read the same list rather than assuming a user who
 * holds one section holds them all.
 */
export const MANAGE_SECTIONS: readonly ManageSection[] = [
  {href: '/manage/hardware', labelKey: 'manage.tab-hardware', privilege: ROOT_PRIVILEGE_EDIT_HARDWARE},
  {href: '/manage/categories', labelKey: 'manage.tab-categories', privilege: PRIVILEGE_CATEGORY_SAVE},
  {href: '/manage/countries', labelKey: 'manage.tab-countries', privilege: PRIVILEGE_COUNTRY_SAVE},
];
