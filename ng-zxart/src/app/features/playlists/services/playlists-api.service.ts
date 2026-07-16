import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable} from 'rxjs';

export interface UserPlaylist {
  id: number;
  title: string;
}

interface PlaylistsResponse {
  playlists: UserPlaylist[];
}

/**
 * Self-service management of the current user's playlists via `/playlists-data/`.
 * Every call returns the full, updated list so the page can re-render from one
 * source of truth.
 */
@Injectable({providedIn: 'root'})
export class PlaylistsApiService {
  private readonly apiUrl = '/playlists-data/';

  constructor(private readonly http: HttpClient) {}

  list(): Observable<UserPlaylist[]> {
    return this.http.get<PlaylistsResponse>(this.apiUrl).pipe(map(r => r?.playlists ?? []));
  }

  create(title: string): Observable<UserPlaylist[]> {
    return this.post('create', {title});
  }

  rename(id: number, title: string): Observable<UserPlaylist[]> {
    return this.post('rename', {id, title});
  }

  remove(id: number): Observable<UserPlaylist[]> {
    return this.post('delete', {id});
  }

  private post(action: string, body: Record<string, unknown>): Observable<UserPlaylist[]> {
    return this.http.post<PlaylistsResponse>(`${this.apiUrl}?action=${action}`, body).pipe(map(r => r?.playlists ?? []));
  }
}
