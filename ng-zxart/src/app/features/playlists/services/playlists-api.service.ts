import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, defer, Observable, of} from 'rxjs';
import {catchError, map, tap} from 'rxjs/operators';
import {PlaylistsResponse, UserPlaylist} from '../models/user-playlist';

/**
 * Self-service management of the current user's playlists via `/playlists-data/`.
 * Every call returns the full, updated list, which is kept here as the single
 * source of truth — `null` means "not loaded yet".
 */
@Injectable({providedIn: 'root'})
export class PlaylistsApiService {
  private readonly apiUrl = '/playlists-data/';
  private readonly store = new BehaviorSubject<UserPlaylist[] | null>(null);
  private loading = false;

  /** Emits `null` until the first response arrives, then the current list. */
  readonly playlists$: Observable<UserPlaylist[] | null> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.load();
    }
    return this.store.asObservable();
  });

  constructor(private readonly http: HttpClient) {}

  create(title: string): Observable<UserPlaylist[]> {
    return this.post('create', {title});
  }

  rename(id: number, title: string): Observable<UserPlaylist[]> {
    return this.post('rename', {id, title});
  }

  remove(id: number): Observable<UserPlaylist[]> {
    return this.post('delete', {id});
  }

  private load(): void {
    this.loading = true;
    this.http.get<PlaylistsResponse>(this.apiUrl).pipe(
      map(response => response?.playlists ?? []),
      catchError(() => of([])),
    ).subscribe(playlists => {
      this.loading = false;
      this.store.next(playlists);
    });
  }

  private post(action: string, body: Record<string, unknown>): Observable<UserPlaylist[]> {
    return this.http.post<PlaylistsResponse>(`${this.apiUrl}?action=${action}`, body).pipe(
      map(response => response?.playlists ?? []),
      catchError(() => of(this.store.getValue() ?? [])),
      tap(playlists => this.store.next(playlists)),
    );
  }
}
