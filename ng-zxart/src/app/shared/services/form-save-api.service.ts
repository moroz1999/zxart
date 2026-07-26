import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {from, Observable} from 'rxjs';
import {concatMap, map, switchMap, toArray} from 'rxjs/operators';
import {FormCreateEntityType} from '../models/form-create-entity-type';

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
 * Generic entity form save. Existing entities use `/ajax/`; new entities use
 * `/formdata/`. Both endpoints feed the submitted values into the standard
 * CMS form pipeline and return the saved element id.
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
    this.appendPayload(body, base, payload);

    const save$ = this.http.post<{id: number}>('/ajax/', body);
    // a field marked for removal with no replacement file is deleted after save
    const removals = this.uploads(payload)
      .filter(upload => upload.remove && !upload.file)
      .map(upload => upload.field);
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

  create(
    entityType: FormCreateEntityType,
    payload: FormSavePayload,
    year?: number,
  ): Observable<{id: number}> {
    const body = new FormData();
    body.append('entityType', entityType);
    if (year !== undefined) {
      body.append('year', String(year));
    }
    this.appendPayload(body, 'fields', payload);
    return this.http.post<{id: number}>('/formdata/', body);
  }

  private appendPayload(body: FormData, base: string, payload: FormSavePayload): void {
    for (const [field, value] of Object.entries(payload.fields)) {
      this.appendField(body, `${base}[${field}]`, value);
    }
    for (const [field, byLang] of Object.entries(payload.multilang ?? {})) {
      for (const [langId, value] of Object.entries(byLang)) {
        body.append(`${base}[${langId}][${field}]`, value);
      }
    }

    for (const upload of this.uploads(payload)) {
      if (upload.file) {
        body.append(`${base}[${upload.field}]`, upload.file, upload.file.name);
      }
    }
    for (const [prop, selectorFiles] of Object.entries(payload.fileSelectors ?? {})) {
      for (const file of selectorFiles) {
        body.append(`${base}[${prop}][]`, file, file.name);
      }
    }
  }

  private uploads(payload: FormSavePayload): FileUploadField[] {
    return [...(payload.image ? [payload.image] : []), ...(payload.files ?? [])];
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
