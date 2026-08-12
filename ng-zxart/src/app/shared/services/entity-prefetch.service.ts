import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {Observable} from 'rxjs';
import {shareReplay} from 'rxjs/operators';

/**
 * Starts an entity request before the page that needs it exists.
 *
 * A routed page ships in its own chunk, so its API service cannot ask for
 * anything until that chunk — and everything it imports — has been downloaded
 * and parsed. On a phone connection that is over a second during which the
 * connection sits idle. A route resolver primes the response here instead, from
 * the initial bundle, and the page's own API service picks the in-flight
 * request up when it finally runs.
 *
 * The cache only ever holds the entries of the navigation in flight: each
 * prefetch replaces the previous set, and an entry is handed over on first
 * read, so a later visit to the same page fetches fresh data exactly as it does
 * without a resolver.
 */
@Injectable({providedIn: 'root'})
export class EntityPrefetchService {
  private readonly entries = new Map<string, Observable<unknown>>();

  constructor(private readonly http: HttpClient) {}

  /**
   * Issues the requests and keeps their responses. Subscribing here is what
   * puts them on the wire; `shareReplay` then holds the value for the reader.
   */
  prefetch(urls: readonly string[], params: Record<string, string | number>): void {
    this.entries.clear();
    for (const url of urls) {
      const response$ = this.request(url, params);
      this.entries.set(this.buildKey(url, params), response$);
      response$.subscribe({error: () => undefined});
    }
  }

  /** The prefetched response for this exact request, or a fresh one. */
  get<T>(url: string, params: Record<string, string | number>): Observable<T> {
    const key = this.buildKey(url, params);
    const prefetched = this.entries.get(key);
    if (prefetched) {
      this.entries.delete(key);
      return prefetched as Observable<T>;
    }
    return this.request<T>(url, params);
  }

  private request<T>(url: string, params: Record<string, string | number>): Observable<T> {
    let httpParams = new HttpParams();
    for (const [name, value] of Object.entries(params)) {
      httpParams = httpParams.set(name, String(value));
    }
    return this.http.get<T>(url, {params: httpParams}).pipe(
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }

  private buildKey(url: string, params: Record<string, string | number>): string {
    const parts = Object.entries(params)
      .map(([name, value]) => `${name}=${value}`)
      .sort();
    return `${url}?${parts.join('&')}`;
  }
}
