import {AuthorshipInput, ImportOriginInput, MembershipInput} from '../models/entity-save';
import {ImportOriginFields} from '../ui/zx-import-origins-editor/zx-import-origins-editor.models';
import {MemberFields} from '../ui/zx-member-role-editor/zx-member-role-editor.models';

const DATE = /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/;

/**
 * A `d.m.Y` date as the Unix timestamp of its midnight in UTC, the server's
 * time zone; 0 for an empty or malformed date.
 */
export function dateToTimestamp(value: string): number {
  const match = DATE.exec(value.trim());
  if (!match) {
    return 0;
  }
  const [day, month, year] = [Number(match[1]), Number(match[2]), Number(match[3])];
  const date = new Date(Date.UTC(year, month - 1, day));
  if (date.getUTCDate() !== day || date.getUTCMonth() !== month - 1) {
    return 0;
  }
  return date.getTime() / 1000;
}

/** The groups or productions an author takes part in, from the member editor. */
export function membershipInputs(fields: MemberFields): MembershipInput[] {
  return memberEntries(fields).map(([id, roles, startDate, endDate]) => ({elementId: id, roles, startDate, endDate}));
}

/** The authors of an element or the members of a group, from the member editor. */
export function authorshipInputs(fields: MemberFields): AuthorshipInput[] {
  return memberEntries(fields).map(([id, roles, startDate, endDate]) => ({authorId: id, roles, startDate, endDate}));
}

/** The portal ids from the import origins editor; rows without an id are left out. */
export function importOriginInputs(fields: ImportOriginFields): ImportOriginInput[] {
  return Object.values(fields)
    .map(item => ({origin: item.origin, importId: item.importId.trim()}))
    .filter(item => item.importId !== '');
}

function memberEntries(fields: MemberFields): [number, string[], number, number][] {
  return Object.entries(fields.roles).map(([id, roles]) => [
    Number(id),
    roles,
    dateToTimestamp(fields.startDates[id] ?? ''),
    dateToTimestamp(fields.endDates[id] ?? ''),
  ]);
}
