import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';

export interface ElementActionResult {
  /** Id of the element the action produced. */
  readonly id: number;
}

/**
 * Runs an element action through its own SPA data endpoint: `POST {endpoint}?id=`
 * plus any extra query parameters the action needs.
 * Errors arrive as HTTP error statuses with an `errorMessage` body.
 */
@Injectable({providedIn: 'root'})
export class ElementActionApiService {
  constructor(private readonly http: HttpClient) {}

  run(endpoint: string, elementId: number, params: Readonly<Record<string, string>> = {}): Observable<ElementActionResult> {
    return this.http.post<ElementActionResult>(endpoint, null, {params: {...params, id: elementId}});
  }
}
