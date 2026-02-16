import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of, switchMap} from 'rxjs';
import {map, shareReplay, tap} from 'rxjs/operators';
import {ApiResponse, PreferenceDto} from '../models/preference.dto';
import {CurrentUserService} from '../../../shared/services/current-user.service';

const STORAGE_KEY = 'zxart_preferences';

interface StoredPreferences {
  userId: number | null;
  preferences: PreferenceDto[];
}

@Injectable({
  providedIn: 'root'
})
export class UserPreferencesService {
  private readonly isBrowser: boolean;
  private initialized$: Observable<PreferenceDto[]> | null = null;
  private defaults$: Observable<PreferenceDto[]> | null = null;

  constructor(
    private http: HttpClient,
    private currentUserService: CurrentUserService,
    @Inject(PLATFORM_ID) platformId: object
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

  initialize(): Observable<PreferenceDto[]> {
    if (!this.isBrowser) {
      return of([]);
    }

    if (this.initialized$) {
      return this.initialized$;
    }

    this.initialized$ = this.currentUserService.loadUser().pipe(
      switchMap(() => {
        const currentUserId = this.currentUserService.userId;
        const stored = this.loadStoredData();

        if (stored && stored.userId !== currentUserId) {
          this.clearStorage();
        }

        if (!this.currentUserService.isAuthenticated) {
          return of(this.loadFromStorage());
        }

        if (stored && stored.userId === currentUserId && stored.preferences.length > 0) {
          return of(stored.preferences);
        }

        return this.fetchFromServer();
      }),
      shareReplay(1)
    );

    return this.initialized$;
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

    if (!this.currentUserService.isAuthenticated) {
      return of(this.loadFromStorage());
    }

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

  setPreferences(items: {code: string; value: string}[]): Observable<PreferenceDto[]> {
    for (const item of items) {
      this.savePreferenceToStorage(item.code, item.value);
    }

    if (!this.currentUserService.isAuthenticated) {
      return of(this.loadFromStorage());
    }

    const body = new HttpParams()
      .set('batch', JSON.stringify(items));

    return this.http.put<ApiResponse<PreferenceDto[]>>('/userpreferences/', body, {
      headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        throw new Error(response.errorMessage || 'Failed to save preferences');
      }),
      tap(prefs => this.saveToStorage(prefs))
    );
  }

  getDefaults(): Observable<PreferenceDto[]> {
    if (this.defaults$) {
      return this.defaults$;
    }
    this.defaults$ = this.http.get<ApiResponse<PreferenceDto[]>>('/userpreferences/', {
      params: {action: 'defaults'}
    }).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        return [];
      }),
      shareReplay(1)
    );
    return this.defaults$;
  }

  getDefaultValue(code: string): Observable<string | undefined> {
    return this.getDefaults().pipe(
      map(defaults => defaults.find(d => d.code === code)?.value)
    );
  }

  syncFromServer(): Observable<PreferenceDto[]> {
    return this.initialize();
  }

  private fetchFromServer(): Observable<PreferenceDto[]> {
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

  private loadStoredData(): StoredPreferences | null {
    if (!this.isBrowser) {
      return null;
    }
    const stored = localStorage.getItem(STORAGE_KEY);
    if (!stored) {
      return null;
    }
    try {
      const data = JSON.parse(stored);
      if (data && Array.isArray(data.preferences)) {
        return data as StoredPreferences;
      }
      if (Array.isArray(data)) {
        return {userId: null, preferences: data};
      }
      return null;
    } catch {
      return null;
    }
  }

  private loadFromStorage(): PreferenceDto[] {
    const stored = this.loadStoredData();
    return stored ? stored.preferences : [];
  }

  private saveToStorage(prefs: PreferenceDto[]): void {
    if (!this.isBrowser) {
      return;
    }
    const data: StoredPreferences = {
      userId: this.currentUserService.userId,
      preferences: prefs
    };
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }

  private clearStorage(): void {
    if (!this.isBrowser) {
      return;
    }
    localStorage.removeItem(STORAGE_KEY);
  }
}
