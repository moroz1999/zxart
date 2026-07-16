import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {TagListItem} from '../models/tag-list-item';

@Injectable({
  providedIn: 'root',
})
export class TagsListService {
  constructor(private readonly http: HttpClient) {}

  getTags(section: string): Observable<TagListItem[]> {
    return this.http.get<{items: TagListItem[]}>('/tags-list-data/', {params: {section}}).pipe(
      map(response => response?.items ?? []),
      catchError(() => of([])),
    );
  }
}
