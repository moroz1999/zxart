import {HttpClient} from '@angular/common/http';
import {firstValueFrom, of, throwError} from 'rxjs';
import {CurrentUserService} from '../../../shared/services/current-user.service';
import {LocalStorageService} from '../../../shared/services/local-storage.service';
import {CurrentUser} from '../../../shared/models/current-user';
import {DEFAULT_USER_PREFERENCES} from '../models/default-user-preferences';
import {PreferenceValues} from '../models/preference.dto';
import {UserPreferencesService} from './user-preferences.service';

interface StoredPreferences {
  userId: number | null;
  values: PreferenceValues;
}

class MemoryLocalStorage {
  private readonly values = new Map<string, unknown>();

  get<T>(key: string): T | null {
    return (this.values.get(key) as T | undefined) ?? null;
  }

  set(key: string, value: unknown): void {
    this.values.set(key, value);
  }

  remove(key: string): void {
    this.values.delete(key);
  }
}

describe('UserPreferencesService', () => {
  const loggedUser: CurrentUser = {
    id: 7,
    userName: 'tester',
    authorId: null,
  };
  const anonymousUser: CurrentUser = {
    id: null,
    userName: 'anonymous',
    authorId: null,
  };

  it('overwrites the logged-in local snapshot with the backend snapshot on initialization', async () => {
    const storage = new MemoryLocalStorage();
    storage.set('preferences', {
      userId: loggedUser.id,
      values: {homepage_new_prods_start_year: '0'},
    } satisfies StoredPreferences);
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    http.get.and.returnValue(of([{code: 'homepage_new_prods_start_year', value: '1'}]));
    const service = createService(http, loggedUser, storage);

    await firstValueFrom(service.initialize());

    expect(storage.get<StoredPreferences>('preferences')).toEqual({
      userId: loggedUser.id,
      values: {homepage_new_prods_start_year: '1'},
    });
  });

  it('falls back only to the current logged-in user local snapshot when the backend is unavailable', async () => {
    const storage = new MemoryLocalStorage();
    storage.set('preferences', {
      userId: loggedUser.id,
      values: {theme: 'light'},
    } satisfies StoredPreferences);
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    http.get.and.returnValue(throwError(() => new Error('load failed')));
    const service = createService(http, loggedUser, storage);

    expect(await firstValueFrom(service.initialize())).toEqual({theme: 'light'});

    storage.set('preferences', {
      userId: 99,
      values: {theme: 'dark'},
    } satisfies StoredPreferences);
    const otherSessionService = createService(http, loggedUser, storage);

    expect(await firstValueFrom(otherSessionService.initialize())).toEqual({});
    expect(storage.get<StoredPreferences>('preferences')).toEqual({
      userId: loggedUser.id,
      values: {},
    });
  });

  it('updates localStorage only after a successful logged-in save', async () => {
    const storage = new MemoryLocalStorage();
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    http.get.and.returnValue(of([{code: 'homepage_new_prods_start_year', value: '0'}]));
    http.put.and.returnValue(of([{code: 'homepage_new_prods_start_year', value: '1'}]));
    const service = createService(http, loggedUser, storage);
    await firstValueFrom(service.initialize());

    await firstValueFrom(service.setPreferences([
      {code: 'homepage_new_prods_start_year', value: '1'},
    ]));

    expect(storage.get<StoredPreferences>('preferences')?.values).toEqual({
      homepage_new_prods_start_year: '1',
    });
  });

  it('keeps the previous local snapshot after a failed logged-in save', async () => {
    const storage = new MemoryLocalStorage();
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    http.get.and.returnValue(of([{code: 'homepage_new_prods_start_year', value: '0'}]));
    http.put.and.returnValue(throwError(() => new Error('save failed')));
    const service = createService(http, loggedUser, storage);
    await firstValueFrom(service.initialize());

    await expectAsync(firstValueFrom(service.setPreferences([
      {code: 'homepage_new_prods_start_year', value: '1'},
    ]))).toBeRejected();

    expect(storage.get<StoredPreferences>('preferences')?.values).toEqual({
      homepage_new_prods_start_year: '0',
    });
  });

  it('persists anonymous changes locally without an HTTP request', async () => {
    const storage = new MemoryLocalStorage();
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    const service = createService(http, anonymousUser, storage);
    await firstValueFrom(service.initialize());

    await firstValueFrom(service.setPreference('homepage_new_prods_start_year', '1'));

    expect(http.get).not.toHaveBeenCalled();
    expect(http.put).not.toHaveBeenCalled();
    const stored = storage.get<StoredPreferences>('preferences');
    expect(stored?.userId).toBeNull();
    expect(stored?.values['homepage_new_prods_start_year']).toBe('1');
    expect(Object.keys(stored?.values ?? {}).length).toBe(Object.keys(DEFAULT_USER_PREFERENCES).length);
  });

  it('merges an anonymous local snapshot with frontend defaults without an HTTP request', async () => {
    const storage = new MemoryLocalStorage();
    storage.set('preferences', {
      userId: null,
      preferences: [{code: 'theme', value: 'light'}],
    });
    storage.set('radio-criteria', {minRating: 4});
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    const service = createService(http, anonymousUser, storage);

    const preferences = await firstValueFrom(service.initialize());

    expect(preferences['theme']).toBe('light');
    expect(preferences['homepage_new_prods_start_year']).toBe('1');
    expect(preferences['radio_criteria']).toBe('{"minRating":4}');
    expect(Object.keys(preferences).length).toBe(Object.keys(DEFAULT_USER_PREFERENCES).length);
    expect(http.get).not.toHaveBeenCalled();
    expect(storage.get<StoredPreferences>('preferences')?.values['theme']).toBe('light');
  });

  it('returns frontend defaults to an anonymous user without an HTTP request', async () => {
    const storage = new MemoryLocalStorage();
    const http = jasmine.createSpyObj<HttpClient>('HttpClient', ['get', 'put']);
    const service = createService(http, anonymousUser, storage);

    expect(await firstValueFrom(service.getDefaults())).toEqual(DEFAULT_USER_PREFERENCES);
    expect(http.get).not.toHaveBeenCalled();
  });

  function createService(
    http: HttpClient,
    user: CurrentUser,
    storage: MemoryLocalStorage,
  ): UserPreferencesService {
    const currentUserService = {user$: of(user)} as CurrentUserService;
    return new UserPreferencesService(
      http,
      currentUserService,
      storage as unknown as LocalStorageService,
    );
  }
});
