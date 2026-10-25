/** One group or production an author takes part in. Dates are Unix timestamps, 0 for none. */
export interface MembershipInput {
  elementId: number;
  roles: string[];
  startDate: number;
  endDate: number;
}

/** One author of an element, or one member of a group. Dates are Unix timestamps, 0 for none. */
export interface AuthorshipInput {
  authorId: number;
  roles: string[];
  startDate: number;
  endDate: number;
}

/** An id the entity has on an external portal. */
export interface ImportOriginInput {
  origin: string;
  importId: string;
}

/**
 * The editable fields of an entity as its data endpoint takes them. A field
 * left out keeps its value; an empty string clears it, and 0 clears a
 * reference.
 */
export type EntityFields = Record<string, string | number | boolean | number[] | string[] | null>;

/** A list left out keeps what the entity holds; an empty list clears it. */
export interface AuthorSaveRequest {
  fields: EntityFields;
  /** Language id => the real name in that language. */
  realNames?: Record<string, string>;
  groups?: MembershipInput[];
  prods?: MembershipInput[];
  importOrigins?: ImportOriginInput[];
  removeImage?: boolean;
}

export interface AuthorAliasSaveRequest {
  fields: EntityFields;
  groups?: MembershipInput[];
  prods?: MembershipInput[];
  importOrigins?: ImportOriginInput[];
}

export interface GroupSaveRequest {
  fields: EntityFields;
  members?: AuthorshipInput[];
  importOrigins?: ImportOriginInput[];
  removeImage?: boolean;
}

export interface GroupAliasSaveRequest {
  fields: EntityFields;
  members?: AuthorshipInput[];
  importOrigins?: ImportOriginInput[];
}

export interface ProdSaveRequest {
  fields: EntityFields;
  authors?: AuthorshipInput[];
  importOrigins?: ImportOriginInput[];
}

/** The values every production of a batch upload shares. */
export interface ProdBatchUploadRequest {
  fields: EntityFields;
  authors?: AuthorshipInput[];
}

export interface ReleaseSaveRequest {
  fields: EntityFields;
  authors?: AuthorshipInput[];
  importOrigins?: ImportOriginInput[];
  /** Takes the release's own file away unless a new one is uploaded. */
  removeFile?: boolean;
}

/** The values every release made from the uploaded files shares. */
export interface ReleaseCreateFromFilesRequest {
  fields: EntityFields;
  authors?: AuthorshipInput[];
}

/** Files of each list, by the list's kind (`screenshots`, `inlays`, …). */
export type FilesByKind = Partial<Record<string, File[]>>;

export interface ImageUpload {
  image?: File | null;
}

export interface ProdUploads {
  files?: FilesByKind;
}

export interface ProdBatchUploads {
  releaseFiles: File[];
  files?: FilesByKind;
}

export interface ReleaseUploads {
  file?: File | null;
  files?: FilesByKind;
}

export interface ReleaseCreateUploads {
  releaseFiles: File[];
  files?: FilesByKind;
}

/** Answer of an endpoint that created several elements. */
export interface CreatedIdsResult {
  readonly ids: number[];
}
