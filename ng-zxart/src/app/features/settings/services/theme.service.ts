import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {UserPreferencesService} from './user-preferences.service';
import {Theme} from '../models/preference.dto';

const STORAGE_KEY = 'zxart_theme';
const DEFAULT_THEME: Theme = 'light';

@Injectable({
  providedIn: 'root'
})
export class ThemeService {
  private currentTheme$ = new BehaviorSubject<Theme>(DEFAULT_THEME);
  private initialized = false;
  private isBrowser: boolean;

  constructor(
    private userPreferencesService: UserPreferencesService,
    @Inject(PLATFORM_ID) platformId: object
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

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

    const storedTheme = this.getFromStorage();
    this.applyTheme(storedTheme);

    this.userPreferencesService.getPreferences().pipe(
      catchError(() => of([]))
    ).subscribe(preferences => {
      const themePref = preferences.find(p => p.code === 'theme');
      if (themePref) {
        const theme = this.validateTheme(themePref.value);
        this.applyTheme(theme);
        this.saveToStorage(theme);
      }
    });
  }

  setTheme(theme: Theme): void {
    const previousTheme = this.currentTheme;
    this.applyTheme(theme);
    this.saveToStorage(theme);

    this.userPreferencesService.setPreference('theme', theme).pipe(
      catchError(() => {
        this.applyTheme(previousTheme);
        this.saveToStorage(previousTheme);
        return of([]);
      })
    ).subscribe();
  }

  private applyTheme(theme: Theme): void {
    if (!this.isBrowser) {
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

  private getFromStorage(): Theme {
    if (!this.isBrowser) {
      return DEFAULT_THEME;
    }
    const stored = localStorage.getItem(STORAGE_KEY);
    return this.validateTheme(stored ?? undefined);
  }

  private saveToStorage(theme: Theme): void {
    if (!this.isBrowser) {
      return;
    }
    localStorage.setItem(STORAGE_KEY, theme);
  }
}
