import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {FormDataResponse} from '../models/form-data-response';
import {FormCreateEntityType} from '../models/form-create-entity-type';

/**
 * Generic loader of edit-form values and transient creation drafts
 * (`/formdata/`), shared by every form.
 */
@Injectable({providedIn: 'root'})
export class FormDataApiService {
  constructor(private readonly http: HttpClient) {}

  /**
   * @param refs names of relation fields to resolve to `{id, title}` (the ones
   *             rendered as entity-autocomplete pickers).
   */
  load(id: number, refs: string[] = []): Observable<FormDataResponse> {
    const params: Record<string, string> = {id: String(id)};
    if (refs.length > 0) {
      params['refs'] = refs.join(',');
    }
    return this.request(params);
  }

  /**
   * @param parentId element the form was started from (author, group, party or
   *                 production); the created works are attached to it.
   */
  loadCreate(
    entityType: FormCreateEntityType,
    refs: string[] = [],
    year?: number,
    parentId?: number,
  ): Observable<FormDataResponse> {
    const params: Record<string, string> = {entityType};
    if (refs.length > 0) {
      params['refs'] = refs.join(',');
    }
    if (year !== undefined) {
      params['year'] = String(year);
    }
    if (parentId !== undefined) {
      params['parentId'] = String(parentId);
    }
    return this.request(params);
  }

  private request(params: Record<string, string>): Observable<FormDataResponse> {
    return this.http.get<FormDataResponse>('/formdata/', {params}).pipe(
      catchError(error => of(this.emptyResponse(error?.error?.errorMessage ?? 'form.error-load'))),
      shareReplay({bufferSize: 1, refCount: true}),
    );
  }

  private emptyResponse(errorMessage: string): FormDataResponse {
    return {
      errorMessage,
      entityTitle: '',
      fields: {},
      multilang: {},
      refs: {},
      multiRefs: {},
      images: {},
      files: {},
      fileSelectors: {},
      aiStatuses: {},
      languages: [],
      members: [],
      roles: [],
      subgroups: [],
      categoriesTree: [],
      authorRefs: [],
      originalAuthorRefs: [],
      enums: {},
    };
  }
}
