import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {BehaviorSubject, defer, Observable, of, switchMap, take, throwError} from 'rxjs';
import {catchError, filter, map, shareReplay, tap} from 'rxjs/operators';
import {DEFAULT_USER_PREFERENCES} from '../models/default-user-preferences';
import {PreferenceDto, PreferenceValues} from '../models/preference.dto';
import {CurrentUserService} from '../../../shared/services/current-user.service';
import {LocalStorageService} from '../../../shared/services/local-storage.service';

const STORAGE_KEY = 'preferences';
const LEGACY_RADIO_CRITERIA_KEY = 'radio-criteria';

interface StoredPreferences {
  userId: number | null;
  values: PreferenceValues;
}

@Injectable({
  providedIn: 'root'
})
export class UserPreferencesService {
  private readonly store = new BehaviorSubject<PreferenceValues | null>(null);
  private initialized$: Observable<PreferenceValues> | null = null;
  private defaults$: Observable<PreferenceValues> | null = null;

  readonly preferences$: Observable<PreferenceValues> = defer(() => this.initialize()).pipe(
    switchMap(() => this.store.pipe(
      filter((preferences): preferences is PreferenceValues => preferences !== null),
    )),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private http: HttpClient,
    private currentUserService: CurrentUserService,
    private localStorage: LocalStorageService,
  ) {}

  /**
   * Loads the preferences once per app start. A logged-in user always has them
   * refetched, so the backend overwrites whatever localStorage held — another
   * device may have changed them since. Anonymous visitors have nothing to fetch
   * and keep using localStorage alone.
   */
  initialize(): Observable<PreferenceValues> {
    if (this.initialized$) {
      return this.initialized$;
    }

    this.initialized$ = this.currentUserService.user$.pipe(take(1),
      switchMap(user => {
        const stored = this.loadStoredData();

        if (stored && stored.userId !== user.id) {
          this.clearStorage();
        }

        if (user.userName === 'anonymous') {
          const localValues = this.loadFromStorage(null);
          const legacyRadioCriteria = this.localStorage.get<unknown>(LEGACY_RADIO_CRITERIA_KEY);
          if (localValues['radio_criteria'] === undefined && legacyRadioCriteria !== null) {
            localValues['radio_criteria'] = JSON.stringify(legacyRadioCriteria);
          }
          const values = {
            ...DEFAULT_USER_PREFERENCES,
            ...localValues,
          };
          this.commit(null, values);
          return of(values);
        }

        return this.fetchFromServer(user.id).pipe(
          catchError(() => {
            const values = this.loadFromStorage(user.id);
            this.commit(user.id, values);
            return of(values);
          }),
        );
      }),
      shareReplay({bufferSize: 1, refCount: false}),
    );

    return this.initialized$;
  }

  getPreferences(): PreferenceValues {
    return {...(this.store.getValue() ?? this.loadStoredData()?.values ?? {})};
  }

  getPreference(code: string): string | undefined {
    return this.getPreferences()[code];
  }

  setPreference(code: string, value: string): Observable<PreferenceValues> {
    return this.initialize().pipe(
      take(1),
      switchMap(() => this.currentUserService.user$.pipe(take(1))),
      switchMap(user => {
        if (user.userName === 'anonymous') {
          const values = {...this.getPreferences(), [code]: value};
          this.commit(null, values);
          return of(values);
        }

        const body = new HttpParams()
          .set('code', code)
          .set('value', value);

        return this.http.put<PreferenceDto[]>('/userpreferences/', body, {
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).pipe(
          map(preferences => this.fromDtos(preferences)),
          tap(values => this.commit(user.id, values)),
          catchError(error => throwError(() => this.toError(error, 'Failed to save preference'))),
        );
      }),
    );
  }

  setPreferences(items: PreferenceDto[]): Observable<PreferenceValues> {
    return this.initialize().pipe(
      take(1),
      switchMap(() => this.currentUserService.user$.pipe(take(1))),
      switchMap(user => {
        if (user.userName === 'anonymous') {
          const values = {
            ...this.getPreferences(),
            ...this.fromDtos(items),
          };
          this.commit(null, values);
          return of(values);
        }

        const body = new HttpParams()
          .set('batch', JSON.stringify(items));

        return this.http.put<PreferenceDto[]>('/userpreferences/', body, {
          headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        }).pipe(
          map(preferences => this.fromDtos(preferences)),
          tap(values => this.commit(user.id, values)),
          catchError(error => throwError(() => this.toError(error, 'Failed to save preferences'))),
        );
      }),
    );
  }

  getDefaults(): Observable<PreferenceValues> {
    if (this.defaults$) {
      return this.defaults$;
    }
    this.defaults$ = this.currentUserService.user$.pipe(
      take(1),
      switchMap(user => user.userName === 'anonymous'
        ? of({...DEFAULT_USER_PREFERENCES})
        : this.http.get<PreferenceDto[]>('/userpreferences/', {
          params: {action: 'defaults'},
        }).pipe(map(preferences => this.fromDtos(preferences)))),
      catchError(() => of({})),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    return this.defaults$;
  }

  getDefaultValue(code: string): Observable<string | undefined> {
    return this.getDefaults().pipe(
      map(defaults => defaults[code])
    );
  }

  private fetchFromServer(userId: number | null): Observable<PreferenceValues> {
    return this.http.get<PreferenceDto[]>('/userpreferences/').pipe(
      map(preferences => this.fromDtos(preferences)),
      tap(values => this.commit(userId, values)),
    );
  }

  private loadStoredData(): StoredPreferences | null {
    const data = this.localStorage.get<unknown>(STORAGE_KEY);
    if (!data) {
      return null;
    }
    if (typeof data === 'object' && this.isPreferenceValues((data as StoredPreferences).values)) {
      return data as StoredPreferences;
    }
    if (typeof data === 'object' && Array.isArray((data as {preferences?: unknown}).preferences)) {
      const legacy = data as {userId?: number | null; preferences: PreferenceDto[]};
      return {
        userId: legacy.userId ?? null,
        values: this.fromDtos(legacy.preferences),
      };
    }
    if (Array.isArray(data)) {
      return {userId: null, values: this.fromDtos(data as PreferenceDto[])};
    }
    return null;
  }

  private loadFromStorage(userId: number | null): PreferenceValues {
    const stored = this.loadStoredData();
    if (stored === null || stored.userId !== userId) {
      return {};
    }
    return {...stored.values};
  }

  private commit(userId: number | null, values: PreferenceValues): void {
    const snapshot = {...values};
    this.localStorage.set(STORAGE_KEY, {userId, values: snapshot} satisfies StoredPreferences);
    this.store.next(snapshot);
  }

  private clearStorage(): void {
    this.localStorage.remove(STORAGE_KEY);
  }

  private fromDtos(preferences: readonly PreferenceDto[]): PreferenceValues {
    return Object.fromEntries(
      preferences.map(preference => [preference.code, preference.value]),
    );
  }

  private isPreferenceValues(value: unknown): value is PreferenceValues {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
  }

  private toError(error: {error?: {errorMessage?: string}}, fallbackMessage: string): Error {
    return new Error(error.error?.errorMessage || fallbackMessage);
  }
}
