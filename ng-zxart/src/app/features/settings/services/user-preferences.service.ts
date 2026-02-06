import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable} from 'rxjs';
import {map, tap} from 'rxjs/operators';
import {ApiResponse, PreferenceDto} from '../models/preference.dto';

const STORAGE_KEY = 'zxart_preferences';

@Injectable({
  providedIn: 'root'
})
export class UserPreferencesService {
  private readonly isBrowser: boolean;

  constructor(
    private http: HttpClient,
    @Inject(PLATFORM_ID) platformId: object
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

  getPreferences(): PreferenceDto[] {
    return this.loadFromStorage();
  }

  getPreference(code: string): string | undefined {
    const prefs = this.loadFromStorage();
    return prefs.find(p => p.code === code)?.value;
  }

  setPreference(code: string, value: string): Observable<PreferenceDto[]> {
    this.savePreferenceToStorage(code, value);

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
      }),
      tap(prefs => this.saveToStorage(prefs))
    );
  }

  syncFromServer(): Observable<PreferenceDto[]> {
    return this.http.get<ApiResponse<PreferenceDto[]>>('/userpreferences/').pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      }),
      tap(prefs => this.saveToStorage(prefs))
    );
  }

  private savePreferenceToStorage(code: string, value: string): void {
    const prefs = this.loadFromStorage();
    const existing = prefs.find(p => p.code === code);
    if (existing) {
      existing.value = value;
    } else {
      prefs.push({code, value});
    }
    this.saveToStorage(prefs);
  }

  private loadFromStorage(): PreferenceDto[] {
    if (!this.isBrowser) {
      return [];
    }
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) {
      return [];
    }
    try {
      return JSON.parse(stored);
    } catch {
      return [];
    }
  }

  private saveToStorage(prefs: PreferenceDto[]): void {
    if (!this.isBrowser) {
      return;
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
  }
}
