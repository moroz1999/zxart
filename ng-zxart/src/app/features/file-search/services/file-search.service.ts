import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {FileSearchResult} from '../models/file-search-result';

@Injectable({
  providedIn: 'root',
})
export class FileSearchService {
  constructor(private readonly http: HttpClient) {}

  search(query: string): Observable<FileSearchResult[]> {
    return this.http.get<{items: FileSearchResult[]}>('/file-search-data/', {params: {q: query}}).pipe(
      map(response => response?.items ?? []),
      catchError(() => of([])),
    );
  }
}
