import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, Observable, of, pairwise, startWith} from 'rxjs';
import {catchError, distinctUntilChanged, filter, shareReplay, switchMap} from 'rxjs/operators';
import {BackendLinks} from '../models/backend-links';
import {CurrentLanguageService} from './current-language.service';
import {LocalStorageService} from '../../../shared/services/local-storage.service';
import {CurrentUserService} from '../../../shared/services/current-user.service';

@Injectable({
  providedIn: 'root',
})
export class BackendLinksService {
  private static readonly STORAGE_KEY_PREFIX = 'backend-links:';

  private readonly store = new BehaviorSubject<BackendLinks | null>(null);
  private loadingCode: string | null = null;
  private loadedCode: string | null = null;
  private readonly cachedLanguageCodes = new Set<string>();

  readonly links$: Observable<BackendLinks> = this.currentLanguageService.languageCode$.pipe(
    distinctUntilChanged(),
    switchMap(code => {
      if (this.loadedCode !== code) {
        this.store.next(null);
        const cached = this.localStorage.get<BackendLinks>(BackendLinksService.STORAGE_KEY_PREFIX + code);
        if (cached !== null) {
          this.loadedCode = code;
          this.store.next(cached);
        } else if (this.loadingCode !== code) {
          this.fetchLinks(code);
        }
      }
      return this.store.pipe(filter((l): l is BackendLinks => l !== null));
    }),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly http: HttpClient,
    private readonly currentLanguageService: CurrentLanguageService,
    private readonly localStorage: LocalStorageService,
    private readonly currentUserService: CurrentUserService,
  ) {
    this.currentUserService.isAuthenticated$.pipe(
      startWith(null as boolean | null),
      pairwise(),
    ).subscribe(([prev, curr]) => {
      if (prev !== null && prev !== curr) {
        this.invalidateCache();
      }
    });
  }

  private invalidateCache(): void {
    for (const code of this.cachedLanguageCodes) {
      this.localStorage.remove(BackendLinksService.STORAGE_KEY_PREFIX + code);
    }
    this.cachedLanguageCodes.clear();
    this.loadedCode = null;
    this.store.next(null);
  }

  private fetchLinks(code: string): void {
    this.loadingCode = code;
    this.http.get<BackendLinks>('/backend-links/', {params: {lang: code}}).pipe(
      catchError(() => of(null)),
    ).subscribe(links => {
      if (this.loadingCode === code) {
        this.loadingCode = null;
      }
      if (links !== null && this.loadedCode !== code) {
        this.loadedCode = code;
        this.cachedLanguageCodes.add(code);
        this.localStorage.set(BackendLinksService.STORAGE_KEY_PREFIX + code, links);
        this.store.next(links);
      }
    });
  }
}
