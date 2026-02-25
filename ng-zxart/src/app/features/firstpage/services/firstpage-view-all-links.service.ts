import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay, tap} from 'rxjs/operators';
import {CatalogueBaseUrlsResponse} from '../models/firstpage-view-all-links';

interface CachedBaseUrls {
  data: CatalogueBaseUrlsResponse;
  timestamp: number;
}

const STORAGE_KEY = 'firstpage_base_urls';
const CACHE_TTL_MS = 24 * 60 * 60 * 1000;

@Injectable({
  providedIn: 'root'
})
export class FirstpageViewAllLinksService {
  private baseUrls$?: Observable<CatalogueBaseUrlsResponse>;
  private readonly isBrowser: boolean;

  constructor(
    private http: HttpClient,
    @Inject(PLATFORM_ID) platformId: object,
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

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
    if (!this.isBrowser) {
      return null;
    }
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return null;
      }
      const cached: CachedBaseUrls = JSON.parse(raw);
      if (Date.now() - cached.timestamp < CACHE_TTL_MS) {
        return cached.data;
      }
      localStorage.removeItem(STORAGE_KEY);
    } catch {
      // ignore
    }
    return null;
  }

  private saveToStorage(data: CatalogueBaseUrlsResponse): void {
    if (!this.isBrowser) {
      return;
    }
    try {
      const cached: CachedBaseUrls = {data, timestamp: Date.now()};
      localStorage.setItem(STORAGE_KEY, JSON.stringify(cached));
    } catch {
      // ignore
    }
  }
}
