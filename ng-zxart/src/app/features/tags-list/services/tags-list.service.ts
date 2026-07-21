import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay} from 'rxjs/operators';
import {TagListItem} from '../models/tag-list-item';
import {TagsListResult} from '../models/tags-list-result';

@Injectable({
  providedIn: 'root',
})
export class TagsListService {
  constructor(private readonly http: HttpClient) {}

  getTags(section: string, minimumAmount: number): Observable<TagsListResult> {
    return this.http.get<{items: TagListItem[]}>('/tags-list-data/', {
      params: {section, minimumAmount: String(minimumAmount)},
    }).pipe(
      map(response => ({items: response?.items ?? [], error: false})),
      catchError(() => of({items: [], error: true})),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
