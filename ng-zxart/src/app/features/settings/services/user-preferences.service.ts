import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, throwError} from 'rxjs';
import {catchError, map} from 'rxjs/operators';
import {ApiResponse, PreferenceDto} from '../models/preference.dto';

@Injectable({
  providedIn: 'root'
})
export class UserPreferencesService {
  constructor(private http: HttpClient) {}

  getPreferences(): Observable<PreferenceDto[]> {
    return this.http.get<ApiResponse<PreferenceDto[]>>('/userpreferences/').pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      }),
      catchError(err => throwError(() => err))
    );
  }

  setPreference(code: string, value: string): Observable<PreferenceDto[]> {
    const body = new HttpParams()
      .set('code', code)
      .set('value', value);

    return this.http.put<ApiResponse<PreferenceDto[]>>('/userpreferences/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        throw new Error(response.errorMessage || 'Failed to save preference');
      })
    );
  }
}
