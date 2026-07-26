import {Injectable} from '@angular/core';
import {HttpClient, HttpErrorResponse, HttpParams} from '@angular/common/http';
import {from, Observable, of} from 'rxjs';
import {catchError, concatMap, map, switchMap, toArray} from 'rxjs/operators';
import {FormCreateEntityType} from '../models/form-create-entity-type';
import {FileUploadField, FormFieldValue, FormSavePayload, FormSaveResult} from '../models/form-save';

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
  save(id: number, payload: FormSavePayload, action = 'publicReceive'): Observable<FormSaveResult> {
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
        catchError(error => of(this.failedResult(error))),
      );
    }
    return save$.pipe(catchError(error => of(this.failedResult(error))));
  }

  create(
    entityType: FormCreateEntityType,
    payload: FormSavePayload,
    year?: number,
    parentId?: number,
  ): Observable<FormSaveResult> {
    const body = new FormData();
    body.append('entityType', entityType);
    if (year !== undefined) {
      body.append('year', String(year));
    }
    if (parentId !== undefined) {
      body.append('parentId', String(parentId));
    }
    this.appendPayload(body, 'fields', payload);
    return this.http.post<FormSaveResult>('/formdata/', body).pipe(
      catchError(error => of(this.failedResult(error))),
    );
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
    return this.http.get('/ajax/', {params, responseType: 'text'}).pipe(catchError(() => of(null)));
  }

  /** Deletes one file element of a multi-file selector live (shared `delete` action). */
  deleteFileElement(fileId: number): Observable<unknown> {
    const params = new HttpParams().set('id', String(fileId)).set('action', 'delete');
    return this.http.get('/ajax/', {params, responseType: 'text'}).pipe(catchError(() => of(null)));
  }

  private deleteFile(id: number, field: string): Observable<unknown> {
    const params = new HttpParams()
      .set('id', String(id))
      .set('action', 'deleteFile')
      .set('file', field);
    return this.http.get('/ajax/', {params, responseType: 'text'}).pipe(catchError(() => of(null)));
  }

  private failedResult(error: unknown): FormSaveResult {
    return {
      id: 0,
      errorMessage: error instanceof HttpErrorResponse
        ? error.error?.errorMessage ?? 'form.error-save'
        : 'form.error-save',
    };
  }
}
