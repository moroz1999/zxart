import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ActiveAuthor} from '../models/active-author';

@Injectable({
  providedIn: 'root',
})
export class ActiveAuthorsService {
  constructor(private http: HttpClient) {}

  getActive(elementId: number, items: string, years: number): Observable<ActiveAuthor[]> {
    const params: Record<string, string> = {
      action: 'active',
      elementId: String(elementId),
      years: String(years),
    };
    if (items) {
      params['items'] = items;
    }

    return this.http.get<{items: ActiveAuthor[]}>('/authorlist/', {params}).pipe(
      map(response => response.items ?? []),
      catchError(() => of([])),
    );
  }
}
