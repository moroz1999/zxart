import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, filter, map, shareReplay} from 'rxjs/operators';
import {LanguageItem} from '../models/language-item';
import {LanguagesService} from './languages.service';
import {CurrentRouteService} from './current-route.service';

@Injectable({
  providedIn: 'root',
})
export class CurrentLanguageService {
  private readonly _languages$: Observable<LanguageItem[]> =
    this.languagesService.getLanguages(this.routeService.pathname).pipe(
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false}),
    );

  readonly languages$: Observable<LanguageItem[]> = this._languages$;

  /** Emits only when the server explicitly marks a language as active. No fallback. */
  readonly activeLanguage$: Observable<LanguageItem> = this._languages$.pipe(
    map(langs => langs.find(l => l.active)),
    filter((lang): lang is LanguageItem => lang !== undefined),
  );

  /** Language code from the active language, e.g. 'rus'. No hardcoded default. */
  readonly languageCode$: Observable<string> = this.activeLanguage$.pipe(
    map(lang => lang.code),
    filter((code): code is string => !!code),
  );

  constructor(
    private languagesService: LanguagesService,
    private routeService: CurrentRouteService,
  ) {}
}
