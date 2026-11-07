import {BehaviorSubject, of} from 'rxjs';
import {afterEach, describe, expect, it, vi} from 'vitest';
import {PreferenceValues} from '../models/preference.dto';
import {UserPreferencesService} from './user-preferences.service';
import {ThemeService} from './theme.service';

function createService(initialPreferences: PreferenceValues) {
  const preferences = new BehaviorSubject<PreferenceValues>(initialPreferences);
  const userPreferencesService = {
    getPreference: vi.fn((code: string) => preferences.value[code]),
    preferences$: preferences.asObservable(),
    setPreference: vi.fn((code: string, value: string | null) => {
      preferences.next({...preferences.value, [code]: value});
      return of(preferences.value);
    }),
  };

  return {
    service: new ThemeService(userPreferencesService as unknown as UserPreferencesService),
    userPreferencesService,
  };
}

describe('ThemeService', () => {
  afterEach(() => {
    document.documentElement.classList.remove('light-mode', 'dark-mode');
  });

  it('uses automatic mode when no theme preference exists', () => {
    const {service} = createService({});

    service.initialize();

    expect(service.currentTheme).toBeNull();
    expect(document.documentElement.classList.contains('light-mode')).toBe(false);
    expect(document.documentElement.classList.contains('dark-mode')).toBe(false);
  });

  it.each(['light', 'dark'] as const)('keeps an existing %s preference', theme => {
    const {service} = createService({theme});

    service.initialize();

    expect(service.currentTheme).toBe(theme);
    expect(document.documentElement.classList.contains(`${theme}-mode`)).toBe(true);
  });

  it('persists null when automatic mode is selected', () => {
    const {service, userPreferencesService} = createService({theme: 'dark'});
    service.initialize();

    service.setTheme(null);

    expect(userPreferencesService.setPreference).toHaveBeenCalledWith('theme', null);
    expect(service.currentTheme).toBeNull();
  });
});
