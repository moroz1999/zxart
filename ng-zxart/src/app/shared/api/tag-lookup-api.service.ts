import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {Tag} from '../models/tag';
import {TagDto} from '../models/tag-dto';

const ENDPOINT = '/tag-lookup/';

interface TagLookupResponse {
  readonly items: TagDto[];
}

/**
 * What a tag picker offers for a typed phrase, through the tag lookup
 * endpoint, the most used tags first. A failed request offers nothing.
 */
@Injectable({providedIn: 'root'})
export class TagLookupApiService {
  constructor(private readonly http: HttpClient) {}

  lookup(query: string): Observable<Tag[]> {
    const trimmed = query.trim();
    if (trimmed === '') {
      return of([]);
    }

    return this.http.get<TagLookupResponse>(ENDPOINT, {params: {query: trimmed}}).pipe(
      map(response => response.items.map(item => new Tag(item))),
      catchError(() => of([])),
    );
  }
}
