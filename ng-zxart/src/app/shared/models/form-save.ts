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
  errorMessage?: string;
}
