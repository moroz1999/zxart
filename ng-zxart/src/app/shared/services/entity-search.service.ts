import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {JsonResponse} from '../models/json-response';
import {EntityRef} from '../models/entity-ref';

interface SearchItem {
  id: number | string;
  title?: string;
  searchTitle?: string;
}

/** Per-type element arrays plus scalar fields like `searchTotal`. */
type SearchResponseData = Record<string, SearchItem[] | number | string>;

/**
 * Generic entity search for relation pickers (country, city, author, group,
 * party, …). Backed by the legacy `ajaxSearch` endpoint, which exposes a
 * per-type filter for every business entity. The same endpoint powers the
 * software tag search.
 */
@Injectable({
  providedIn: 'root',
})
export class EntitySearchService {
  constructor(private readonly http: HttpClient) {}

  /**
   * @param types comma-separated structureTypes, e.g. `country`, `author,authorAlias`
   */
  search(types: string, query: string): Observable<EntityRef[]> {
    const trimmed = query.trim();
    if (trimmed === '') {
      return of([]);
    }

    return this.http
      .get<JsonResponse<SearchResponseData>>(`/ajaxSearch/mode:public/types:${encodeURIComponent(types)}/`, {
        params: {query: trimmed},
      })
      .pipe(
        map(response => this.flatten(response.responseData)),
        catchError(() => of([])),
      );
  }

  private flatten(data: SearchResponseData | null): EntityRef[] {
    if (!data) {
      return [];
    }
    const refs: EntityRef[] = [];
    for (const value of Object.values(data)) {
      // responseData mixes per-type element arrays with scalars (e.g. searchTotal)
      if (!Array.isArray(value)) {
        continue;
      }
      for (const item of value) {
        refs.push({id: Number(item.id), title: item.searchTitle ?? item.title ?? ''});
      }
    }
    return refs;
  }
}
