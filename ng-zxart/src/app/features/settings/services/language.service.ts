import {Injectable} from '@angular/core';
import {TranslateService} from '@ngx-translate/core';
import {BehaviorSubject, EMPTY, Observable, of} from 'rxjs';
import {catchError, distinctUntilChanged, map, tap} from 'rxjs/operators';
import {UserPreferencesService} from './user-preferences.service';
import {LanguageOption} from '../models/language-option';

/** The `language` user-preference code (iso6393 value). */
const PREFERENCE_CODE = 'language';
const DEFAULT_SHORT = 'en';

/** The supported languages, matching the shipped i18n bundles. */
export const SUPPORTED_LANGUAGES: readonly LanguageOption[] = [
  {short: 'en', long: 'eng', title: 'English', flag: '🇬🇧'},
  {short: 'ru', long: 'rus', title: 'Русский', flag: '🇷🇺'},
  {short: 'es', long: 'spa', title: 'Español', flag: '🇪🇸'},
];

/**
 * Owns the interface language on the frontend. The language is a user preference,
 * so it flows through `UserPreferencesService` exactly like the theme: anonymous
 * visitors keep it in localStorage, logged-in users have it fetched from and
 * saved to the backend. The current backend code is sent to the API on every
 * request (see the language interceptor) so localized responses match.
 */
@Injectable({
  providedIn: 'root',
})
export class LanguageService {
  readonly languages: readonly LanguageOption[] = SUPPORTED_LANGUAGES;

  private readonly store = new BehaviorSubject<string>(DEFAULT_SHORT);
  private initialized = false;

  /** Current short code (`en`/`ru`/`es`), reactive. */
  readonly current$: Observable<string> = this.store.asObservable();

  /** Backend iso6393 code (`eng`/`rus`/`spa`), for language-scoped API requests. */
  readonly languageCode$: Observable<string> = this.store.pipe(
    map(short => this.toLong(short)),
    distinctUntilChanged(),
  );

  constructor(
    private readonly translate: TranslateService,
    private readonly userPreferencesService: UserPreferencesService,
  ) {}

  /** Synchronous current backend code, for the HTTP interceptor. */
  get currentLongCode(): string {
    return this.toLong(this.store.getValue());
  }

  /**
   * Applies the stored language immediately (localStorage or English default),
   * then re-applies the value fetched for a logged-in user. Called once at
   * bootstrap before the first navigation. Mirrors `ThemeService.initialize`.
   */
  initialize(): void {
    if (this.initialized) {
      return;
    }
    this.initialized = true;

    this.translate.addLangs(this.languages.map(language => language.short));
    this.translate.setDefaultLang(DEFAULT_SHORT);

    this.apply(this.storedShort());

    this.userPreferencesService.initialize().pipe(
      tap(() => {
        const serverShort = this.storedShort();
        if (serverShort !== this.store.getValue()) {
          this.apply(serverShort);
        }
      }),
      catchError(() => EMPTY),
    ).subscribe();
  }

  /** User-initiated switch: applies and persists the preference (localStorage for anonymous, backend for logged-in). */
  setLanguage(short: string): void {
    if (!this.isSupportedShort(short) || short === this.store.getValue()) {
      return;
    }
    this.apply(short);
    this.userPreferencesService.setPreference(PREFERENCE_CODE, this.toLong(short)).pipe(
      catchError(() => of([])),
    ).subscribe();
  }

  private storedShort(): string {
    return this.toShort(this.userPreferencesService.getPreference(PREFERENCE_CODE)) ?? DEFAULT_SHORT;
  }

  private apply(short: string): void {
    this.translate.use(short);
    document.documentElement.lang = short;
    this.store.next(short);
  }

  private isSupportedShort(short: string): boolean {
    return this.languages.some(language => language.short === short);
  }

  private toLong(short: string): string {
    return this.languages.find(language => language.short === short)?.long ?? 'eng';
  }

  private toShort(long: string | null | undefined): string | null {
    return long ? (this.languages.find(language => language.long === long)?.short ?? null) : null;
  }
}
