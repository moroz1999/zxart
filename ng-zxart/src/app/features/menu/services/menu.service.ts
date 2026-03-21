import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, tap} from 'rxjs/operators';
import {MenuItem} from '../models/menu-item';
import {LocalStorageService} from '../../../shared/services/local-storage.service';

@Injectable({
  providedIn: 'root',
})
export class MenuService {
  private readonly apiUrl = '/menu/';

  constructor(
    private http: HttpClient,
    private localStorage: LocalStorageService,
  ) {}

  getMenuItems(languageCode: string): Observable<MenuItem[]> {
    const cacheKey = `menu-${languageCode}`;
    const cached = this.localStorage.get<MenuItem[]>(cacheKey);
    if (cached !== null) {
      return of(cached);
    }

    return this.http.get<MenuItem[]>(this.apiUrl, {params: {lang: languageCode}}).pipe(
      tap(items => this.localStorage.set(cacheKey, items)),
      catchError(() => of([])),
    );
  }
}
