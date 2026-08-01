import {Injectable} from '@angular/core';
import {BehaviorSubject, EMPTY, Observable} from 'rxjs';
import {catchError, distinctUntilChanged, map, tap} from 'rxjs/operators';
import {UserPreferencesService} from './user-preferences.service';
import {Theme} from '../models/preference.dto';

const DEFAULT_THEME: Theme = 'dark';

@Injectable({
  providedIn: 'root'
})
export class ThemeService {
  private currentTheme$ = new BehaviorSubject<Theme>(DEFAULT_THEME);
  private initialized = false;

  constructor(
    private userPreferencesService: UserPreferencesService
  ) {}

  get theme$(): Observable<Theme> {
    return this.currentTheme$.asObservable();
  }

  get currentTheme(): Theme {
    return this.currentTheme$.value;
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

  setTheme(theme: Theme): void {
    this.userPreferencesService.setPreference('theme', theme).pipe(
      catchError(() => EMPTY)
    ).subscribe();
  }

  private applyTheme(theme: Theme): void {
    if (typeof document === 'undefined') {
      this.currentTheme$.next(theme);
      return;
    }

    const html = document.documentElement;
    html.classList.remove('light-mode', 'dark-mode');
    html.classList.add(`${theme}-mode`);
    this.currentTheme$.next(theme);
  }

  private validateTheme(value: string | undefined): Theme {
    if (value === 'dark' || value === 'light') {
      return value;
    }
    return DEFAULT_THEME;
  }
}
