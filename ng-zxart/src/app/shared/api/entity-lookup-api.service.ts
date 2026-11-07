import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {EntityRef} from '../models/entity-ref';

const ENDPOINT = '/entity-lookup/';

interface EntityLookupResponse {
  readonly items: EntityRef[];
}

/**
 * What a relation picker offers for a typed phrase, through the entity lookup
 * endpoint. A failed request offers nothing.
 */
@Injectable({providedIn: 'root'})
export class EntityLookupApiService {
  constructor(private readonly http: HttpClient) {}

  /**
   * @param types comma-separated structure types, e.g. `country`, `author,authorAlias`
   */
  lookup(types: string, query: string): Observable<EntityRef[]> {
    const trimmed = query.trim();
    if (trimmed === '') {
      return of([]);
    }

    return this.http.get<EntityLookupResponse>(ENDPOINT, {params: {types, query: trimmed}}).pipe(
      map(response => response.items),
      catchError(() => of([])),
    );
  }
}
