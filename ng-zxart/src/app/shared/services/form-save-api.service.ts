import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {from, Observable} from 'rxjs';
import {concatMap, map, switchMap, toArray} from 'rxjs/operators';

/**
 * A form field value posted as `formData[{id}][field]...`. Supports the legacy
 * structures: a scalar, a list (`field[]=v`) or a keyed map (`field[key]=v`,
 * e.g. per-member roles).
 */
export type FormFieldValue = string | readonly FormFieldValue[] | {readonly [key: string]: FormFieldValue};

/** An uploaded file/image field: a new `file` to store and/or `remove` the existing one. */
export interface FileUploadField {
  field: string;
  file: File | null;
  remove: boolean;
}

export interface FormSavePayload {
  /** scalar / list / keyed fields → `formData[{id}][field]...` */
  fields: Record<string, FormFieldValue>;
  /** multi-language fields → `formData[{id}][langId][field]` (language is the outer key) */
  multilang?: Record<string, Record<string, string>>;
  /** single image field (uploaded file and/or removal) — convenience for one-image forms */
  image?: FileUploadField;
  /** multiple file/image fields (uploaded files and/or removals) */
  files?: FileUploadField[];
  /** multi-file selectors → `formData[{id}][prop][]` (new files appended; deletes are live) */
  fileSelectors?: Record<string, File[]>;
}

/**
 * Generic entity form save. Runs the legacy `publicReceive` action through the
 * `/ajax/` runner using the legacy `formData[{id}][...]` field names, so the full
 * proven CMS pipeline (validation, file storage, persistence, privilege check) is
 * reused. Returns the saved (or newly created) element id.
 */
@Injectable({providedIn: 'root'})
export class FormSaveApiService {
  constructor(private readonly http: HttpClient) {}

  /**
   * @param action legacy entity action to run (default `publicReceive`); pass e.g.
   *   `receiveAiForm` for the AI re-queue form. Must use `respondFormSaved` to return `{id}`.
   */
  save(id: number, payload: FormSavePayload, action = 'publicReceive'): Observable<{id: number}> {
    const base = `formData[${id}]`;
    const body = new FormData();
    body.append('id', String(id));
    body.append('action', action);

    for (const [field, value] of Object.entries(payload.fields)) {
      this.appendField(body, `${base}[${field}]`, value);
    }
    for (const [field, byLang] of Object.entries(payload.multilang ?? {})) {
      for (const [langId, value] of Object.entries(byLang)) {
        body.append(`${base}[${langId}][${field}]`, value);
      }
    }

    const uploads = [...(payload.image ? [payload.image] : []), ...(payload.files ?? [])];
    for (const upload of uploads) {
      if (upload.file) {
        body.append(`${base}[${upload.field}]`, upload.file, upload.file.name);
      }
    }
    for (const [prop, selectorFiles] of Object.entries(payload.fileSelectors ?? {})) {
      for (const file of selectorFiles) {
        body.append(`${base}[${prop}][]`, file, file.name);
      }
    }

    const save$ = this.http.post<{id: number}>('/ajax/', body);
    // a field marked for removal with no replacement file is deleted after save
    const removals = uploads.filter(upload => upload.remove && !upload.file).map(upload => upload.field);
    if (removals.length > 0) {
      return save$.pipe(
        switchMap(saved =>
          from(removals).pipe(
            concatMap(field => this.deleteFile(id, field)),
            toArray(),
            map(() => saved),
          ),
        ),
      );
    }
    return save$;
  }

  private appendField(body: FormData, key: string, value: FormFieldValue): void {
    if (Array.isArray(value)) {
      for (const item of value) {
        this.appendField(body, `${key}[]`, item);
      }
    } else if (typeof value === 'object' && value !== null) {
      for (const [k, v] of Object.entries(value)) {
        this.appendField(body, `${key}[${k}]`, v as FormFieldValue);
      }
    } else {
      body.append(key, value as string);
    }
  }

  /** Removes a member (authorship) live via the legacy `deleteAuthor` action. */
  deleteMember(id: number, authorId: number): Observable<unknown> {
    const params = new HttpParams()
      .set('id', String(id))
      .set('action', 'deleteAuthor')
      .set('authorId', String(authorId));
    return this.http.get('/ajax/', {params, responseType: 'text'});
  }

  /** Deletes one file element of a multi-file selector live (shared `delete` action). */
  deleteFileElement(fileId: number): Observable<unknown> {
    const params = new HttpParams().set('id', String(fileId)).set('action', 'delete');
    return this.http.get('/ajax/', {params, responseType: 'text'});
  }

  private deleteFile(id: number, field: string): Observable<unknown> {
    const params = new HttpParams()
      .set('id', String(id))
      .set('action', 'deleteFile')
      .set('file', field);
    return this.http.get('/ajax/', {params, responseType: 'text'});
  }
}
