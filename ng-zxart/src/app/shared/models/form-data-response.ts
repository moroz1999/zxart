import {EntityRef} from './entity-ref';
import {FormFieldValue} from './form-save';
import {MemberRoleItem} from '../ui/zx-member-role-editor/zx-member-role-editor.models';

export interface FormLanguage {
  id: number;
  name: string;
}

/**
 * The element a creation form was started from — the author whose page the
 * upload began on, the party, or the production category. Forms prefill the
 * field this element belongs in.
 */
export interface FormParentRef {
  id: number;
  title: string;
  structureType: string;
}

/** One node of the prod-category tree (flat list ordered depth-first). */
export interface CategoryTreeNode {
  id: number;
  title: string;
  level: number;
  selected: boolean;
}

/**
 * Generic entity form-data response from `/formdata/`.
 * - `fields`    — raw current values for text/checkbox/date inputs.
 * - `multilang` — per-language values: `field -> {languageId: value}`.
 * - `refs`      — single relation fields resolved to id + title (for entity autocompletes).
 * - `multiRefs` — relation lists (ConnectedElements) resolved to [{id, title}] (for multi pickers).
 * - `images`    — display URLs for image fields.
 * - `languages` — available languages (id + name) for multi-language fields.
 * - `members`   — authorship members (id, title, role, period) for entities that have them.
 * - `roles`     — available author-role keys.
 * - `subgroups` — current subgroups (group entities).
 */
export interface FormDataResponse {
  /** Present when the service recovered from a failed form-data request. */
  errorMessage?: string;
  /** Localized display title of the entity being edited. */
  entityTitle: string;
  fields: Record<string, FormFieldValue | null>;
  multilang: Record<string, Record<string, string>>;
  refs: Record<string, EntityRef>;
  multiRefs: Record<string, EntityRef[]>;
  images: Record<string, string>;
  /** Current filename per non-image file field (`file`, `trackerFile`, `exeFile`, …). */
  files: Record<string, string>;
  /** Existing files of each multi-file selector (screenshots, inlays, …). */
  fileSelectors: Record<string, FileSelectorItem[]>;
  /** Current AI queue status per re-queue field, for the AI form (prod/press). */
  aiStatuses: Record<string, string>;
  /** Splittable items grouped, for the prod split form. */
  splitGroups: SplitGroup[];
  languages: FormLanguage[];
  members: MemberRoleItem[];
  roles: string[];
  subgroups: EntityRef[];
  categoriesTree: CategoryTreeNode[];
  /** Flat connected-author list for tune/picture (plain `author` list, no roles). */
  authorRefs: EntityRef[];
  /** Connected original-author list for pictures (`originalAuthor` link list). */
  originalAuthorRefs: EntityRef[];
  /** Enum options; client-owned enums carry their code in both fields. */
  enums: Record<string, EnumOption[]>;
  /** Only on creation forms started from an element; `null` otherwise. */
  parent?: FormParentRef | null;
}

/** One enum option; its label may be a backend label or a client-owned code. */
export interface EnumOption {
  value: string;
  label: string;
}

/** One existing file of a multi-file selector ({@link FormDataResponse.fileSelectors}). */
export interface FileSelectorItem {
  id: number;
  title: string;
  isImage: boolean;
  imageUrl: string | null;
}

/** A group of splittable items for the prod split form ({@link FormDataResponse.splitGroups}). */
export interface SplitGroup {
  group: string;
  items: {key: string; label: string}[];
}
