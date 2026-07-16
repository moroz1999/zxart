import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable} from 'rxjs';
import {FormDataResponse} from '../models/form-data-response';

/**
 * Generic loader of an entity's current edit-form values (`/formdata/`), shared
 * by every form. Saving is done per entity via the legacy `publicReceive` action
 * (run through `/ajax/`).
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
    return this.http.get<FormDataResponse>('/formdata/', {params});
  }
}
