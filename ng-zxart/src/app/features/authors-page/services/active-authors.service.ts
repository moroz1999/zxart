import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, map, shareReplay} from 'rxjs/operators';
import {ActiveAuthor} from '../models/active-author';

@Injectable({providedIn: 'root'})
export class ActiveAuthorsService {
  constructor(private readonly http: HttpClient) {}

  getActive(items: 'graphics' | 'music', years: number): Observable<ActiveAuthor[]> {
    return this.http.get<{items: ActiveAuthor[]}>('/authorlist/', {
      params: {action: 'active', items, years},
    }).pipe(
      map(response => response.items),
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
