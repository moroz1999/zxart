import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay, tap} from 'rxjs/operators';
import {CatalogueBaseUrlsResponse} from '../models/firstpage-view-all-links';
import {LocalStorageService} from '../../../shared/services/local-storage.service';

interface CachedBaseUrls {
  data: CatalogueBaseUrlsResponse;
  timestamp: number;
}

const STORAGE_KEY = 'firstpage-base-urls';
const CACHE_TTL_MS = 24 * 60 * 60 * 1000;

@Injectable({
  providedIn: 'root'
})
export class FirstpageViewAllLinksService {
  private baseUrls$?: Observable<CatalogueBaseUrlsResponse>;

  constructor(
    private http: HttpClient,
    private localStorage: LocalStorageService,
  ) {}

  getBaseUrls(): Observable<CatalogueBaseUrlsResponse> {
    if (!this.baseUrls$) {
      const cached = this.loadFromStorage();
      if (cached) {
        this.baseUrls$ = of(cached);
      } else {
        this.baseUrls$ = this.http.get<CatalogueBaseUrlsResponse>('/firstpage/?action=catalogueBaseUrls').pipe(
          tap(data => this.saveToStorage(data)),
          catchError(() => of({prodCatalogueBaseUrl: null, graphicsBaseUrl: null, musicBaseUrl: null})),
          shareReplay({bufferSize: 1, refCount: true})
        );
      }
    }
    return this.baseUrls$;
  }

  private loadFromStorage(): CatalogueBaseUrlsResponse | null {
    const cached = this.localStorage.get<CachedBaseUrls>(STORAGE_KEY);
    if (!cached) {
      return null;
    }
    if (Date.now() - cached.timestamp < CACHE_TTL_MS) {
      return cached.data;
    }
    this.localStorage.remove(STORAGE_KEY);
    return null;
  }

  private saveToStorage(data: CatalogueBaseUrlsResponse): void {
    this.localStorage.set(STORAGE_KEY, {data, timestamp: Date.now()} satisfies CachedBaseUrls);
  }
}
