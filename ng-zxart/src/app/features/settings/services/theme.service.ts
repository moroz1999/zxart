import {Injectable} from '@angular/core';
import {BehaviorSubject, EMPTY, Observable} from 'rxjs';
import {catchError, distinctUntilChanged, map, tap} from 'rxjs/operators';
import {UserPreferencesService} from './user-preferences.service';
import {Theme, ThemePreference} from '../models/preference.dto';

const DEFAULT_EFFECTIVE_THEME: Theme = 'dark';

@Injectable({
  providedIn: 'root'
})
export class ThemeService {
  private readonly currentPreference$ = new BehaviorSubject<ThemePreference>(null);
  private readonly effectiveTheme$ = new BehaviorSubject<Theme>(this.resolveSystemTheme());
  private initialized = false;

  constructor(
    private userPreferencesService: UserPreferencesService
  ) {}

  get theme$(): Observable<Theme> {
    return this.effectiveTheme$.asObservable();
  }

  get preference$(): Observable<ThemePreference> {
    return this.currentPreference$.asObservable();
  }

  get currentTheme(): ThemePreference {
    return this.currentPreference$.value;
  }

  initialize(): void {
    if (this.initialized) {
      return;
    }
    this.initialized = true;

    const storedValue = this.userPreferencesService.getPreference('theme');
    const theme = this.validateTheme(storedValue);
    this.applyTheme(theme);

    this.userPreferencesService.preferences$.pipe(
      map(preferences => preferences['theme']),
      map(value => this.validateTheme(value)),
      distinctUntilChanged(),
      tap(serverTheme => this.applyTheme(serverTheme)),
      catchError(() => EMPTY)
    ).subscribe();
  }

  setTheme(theme: ThemePreference): void {
    this.userPreferencesService.setPreference('theme', theme).pipe(
      catchError(() => EMPTY)
    ).subscribe();
  }

  private applyTheme(theme: ThemePreference): void {
    if (typeof document === 'undefined') {
      this.currentPreference$.next(theme);
      this.effectiveTheme$.next(theme ?? DEFAULT_EFFECTIVE_THEME);
      return;
    }

    const html = document.documentElement;
    html.classList.remove('light-mode', 'dark-mode');
    if (theme !== null) {
      html.classList.add(`${theme}-mode`);
    }
    this.currentPreference$.next(theme);
    this.effectiveTheme$.next(theme ?? this.resolveSystemTheme());
  }

  private validateTheme(value: string | null | undefined): ThemePreference {
    if (value === 'dark' || value === 'light') {
      return value;
    }
    return null;
  }

  private resolveSystemTheme(): Theme {
    if (typeof window === 'undefined' || typeof window.matchMedia !== 'function') {
      return DEFAULT_EFFECTIVE_THEME;
    }
    return window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark';
  }
}
