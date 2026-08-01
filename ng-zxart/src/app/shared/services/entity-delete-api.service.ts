import {HttpClient, HttpParams} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, map} from 'rxjs/operators';

/**
 * Deletion of one entity through the legacy `publicDelete` action. The backend
 * checks the `publicDelete` privilege itself and answers `{"success": true}`
 * only when the element was really removed; the caller owns the route to go to
 * afterwards.
 */
@Injectable({providedIn: 'root'})
export class EntityDeleteApiService {
  constructor(private readonly http: HttpClient) {}

  delete(id: number): Observable<boolean> {
    const body = new HttpParams()
      .set('id', String(id))
      .set('action', 'publicDelete');

    return this.http.post<{success?: boolean}>('/ajax/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    }).pipe(
      map(result => result?.success === true),
      catchError(() => of(false)),
    );
  }
}
