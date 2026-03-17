import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable, of, switchMap, take, throwError} from 'rxjs';
import {catchError, map, shareReplay, tap} from 'rxjs/operators';
import {PreferenceDto} from '../models/preference.dto';
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
  private initialized$: Observable<PreferenceDto[]> | null = null;
  private defaults$: Observable<PreferenceDto[]> | null = null;

  constructor(
    private http: HttpClient,
    private currentUserService: CurrentUserService,
  ) {}

  initialize(): Observable<PreferenceDto[]> {
    if (this.initialized$) {
      return this.initialized$;
    }

    this.initialized$ = this.currentUserService.user$.pipe(take(1),
      switchMap(user => {
        const currentUserId = user.id;
        const stored = this.loadStoredData();

        if (stored && stored.userId !== currentUserId) {
          this.clearStorage();
        }

        if (user.userName === 'anonymous') {
          return of(this.loadFromStorage());
        }

        if (stored && stored.userId === currentUserId && stored.preferences.length > 0) {
          return of(stored.preferences);
        }

        return this.fetchFromServer(currentUserId);
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

    return this.currentUserService.user$.pipe(
      take(1),
      switchMap(user => {
        if (user.userName === 'anonymous') {
          return of(this.loadFromStorage());
        }

        const body = new HttpParams()
          .set('code', code)
          .set('value', value);

        return this.http.put<PreferenceDto[]>('/userpreferences/', body, {
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).pipe(
          tap(prefs => this.saveToStorage(user.id, prefs)),
          catchError(err => throwError(() => new Error(err.error?.errorMessage || 'Failed to save preference')))
        );
      }),
    );
  }

  setPreferences(items: {code: string; value: string}[]): Observable<PreferenceDto[]> {
    for (const item of items) {
      this.savePreferenceToStorage(item.code, item.value);
    }

    return this.currentUserService.user$.pipe(
      take(1),
      switchMap(user => {
        if (user.userName === 'anonymous') {
          return of(this.loadFromStorage());
        }

        const body = new HttpParams()
          .set('batch', JSON.stringify(items));

        return this.http.put<PreferenceDto[]>('/userpreferences/', body, {
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).pipe(
          tap(prefs => this.saveToStorage(user.id, prefs)),
          catchError(err => throwError(() => new Error(err.error?.errorMessage || 'Failed to save preferences')))
        );
      }),
    );
  }

  getDefaults(): Observable<PreferenceDto[]> {
    if (this.defaults$) {
      return this.defaults$;
    }
    this.defaults$ = this.http.get<PreferenceDto[]>('/userpreferences/', {
      params: {action: 'defaults'}
    }).pipe(
      catchError(() => of([])),
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

  private fetchFromServer(userId: number | null): Observable<PreferenceDto[]> {
    return this.http.get<PreferenceDto[]>('/userpreferences/').pipe(
      tap(prefs => this.saveToStorage(userId, prefs))
    );
  }

  private savePreferenceToStorage(code: string, value: string): void {
    const stored = this.loadStoredData();
    const prefs = stored ? stored.preferences : [];
    const existing = prefs.find(p => p.code === code);
    if (existing) {
      existing.value = value;
    } else {
      prefs.push({code, value});
    }
    this.saveToStorage(stored?.userId ?? null, prefs);
  }

  private loadStoredData(): StoredPreferences | null {
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

  private saveToStorage(userId: number | null, prefs: PreferenceDto[]): void {
    const data: StoredPreferences = {userId, preferences: prefs};
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
  }

  private clearStorage(): void {
    localStorage.removeItem(STORAGE_KEY);
  }
}
