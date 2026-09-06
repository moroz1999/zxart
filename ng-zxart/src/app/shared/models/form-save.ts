export type FormFieldValue = string | readonly FormFieldValue[] | {readonly [key: string]: FormFieldValue};

export interface FileUploadField {
  field: string;
  file: File | null;
  remove: boolean;
}

export interface FormSavePayload {
  fields: Record<string, FormFieldValue>;
  multilang?: Record<string, Record<string, string>>;
  image?: FileUploadField;
  files?: FileUploadField[];
  /** Fields posted as `field[]`, so the backend receives a list of uploads. */
  fileSelectors?: Record<string, File[]>;
}

export interface FormSaveResult {
  id: number;
  /**
   * Every element the submit created, when a form creates more than one — the
   * release form creates one release per uploaded file. `id` is the first of them.
   */
  ids?: number[];
  errorMessage?: string;
}
