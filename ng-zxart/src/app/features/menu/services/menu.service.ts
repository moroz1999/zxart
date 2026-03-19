import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, tap} from 'rxjs/operators';
import {MenuItem} from '../models/menu-item';

@Injectable({
  providedIn: 'root',
})
export class MenuService {
  private readonly apiUrl = '/menu/';

  constructor(private http: HttpClient) {}

  getMenuItems(languageCode: string): Observable<MenuItem[]> {
    const cacheKey = `menu:${languageCode}`;
    const cached = localStorage.getItem(cacheKey);
    if (cached !== null) {
      try {
        return of(JSON.parse(cached) as MenuItem[]);
      } catch {
        localStorage.removeItem(cacheKey);
      }
    }

    return this.http.get<MenuItem[]>(this.apiUrl, {params: {lang: languageCode}}).pipe(
      tap(items => localStorage.setItem(cacheKey, JSON.stringify(items))),
      catchError(() => of([])),
    );
  }
}
