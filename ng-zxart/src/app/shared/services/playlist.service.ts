import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {HttpClient, HttpParams} from '@angular/common/http';
import {map, Observable, of, tap} from 'rxjs';
import {PlaylistDto} from '../models/playlist.model';
import {JsonResponse} from '../models/json-response';

interface PlaylistItemData {
  id: number;
  playlistIds: number[];
}

interface PlaylistResponseData {
  zxMusic?: PlaylistItemData[];
  zxPicture?: PlaylistItemData[];
  zxProd?: PlaylistItemData[];
  zxRelease?: PlaylistItemData[];
  playlist?: PlaylistDto[];
}

@Injectable({
  providedIn: 'root',
})
export class PlaylistService {
  private readonly apiUrl: string;
  private readonly isBrowser: boolean;
  private playlists: PlaylistDto[] = [];
  private playlistsElementUrl = '';

  constructor(
    @Inject(PLATFORM_ID) platformId: object,
    private http: HttpClient,
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
    this.apiUrl = this.isBrowser ? `//${location.hostname}/ajax/` : '';
    if (this.isBrowser) {
      this.importFromWindow();
    }
  }

  getPlaylists(): PlaylistDto[] {
    return this.playlists;
  }

  fetchPlaylistIds(elementId: number): Observable<number[]> {
    if (!this.isBrowser) {
      return of([]);
    }
    const params = {id: elementId, action: 'getPlaylistIds'};
    return this.http.get<JsonResponse<PlaylistResponseData>>(this.apiUrl, {params: params as any}).pipe(
      map(response => this.extractPlaylistIds(response.responseData, elementId)),
    );
  }

  addToPlaylist(playlistId: number, elementId: number): Observable<number[]> {
    const body = new HttpParams()
      .set('id', elementId)
      .set('action', 'addToPlaylist');

    return this.http.post<JsonResponse<PlaylistResponseData>>(
      `${this.apiUrl}playlistId:${playlistId}/`,
      body.toString(),
      {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    ).pipe(
      map(response => this.extractPlaylistIds(response.responseData, elementId)),
    );
  }

  removeFromPlaylist(playlistId: number, elementId: number): Observable<number[]> {
    const body = new HttpParams()
      .set('id', elementId)
      .set('action', 'removeFromPlaylist');

    return this.http.post<JsonResponse<PlaylistResponseData>>(
      `${this.apiUrl}playlistId:${playlistId}/`,
      body.toString(),
      {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    ).pipe(
      map(response => this.extractPlaylistIds(response.responseData, elementId)),
    );
  }

  createPlaylist(title: string): Observable<PlaylistDto[]> {
    if (!this.playlistsElementUrl) {
      return of(this.playlists);
    }
    const actionUrl = this.playlistsElementUrl.replace(
      this.getRootUrl(),
      this.getRootUrl() + 'ajax/',
    );
    const relativePath = this.playlistsElementUrl.replace(this.getRootUrl(), '/');
    const titleFieldName = `formData[${relativePath}type:playlist/action:receive/][title]`;

    let body = new HttpParams()
      .set('id', 'type:playlist/')
      .set('type', 'playlist')
      .set('action', 'receive')
      .set(titleFieldName, title);

    return this.http.post<JsonResponse<PlaylistResponseData>>(
      actionUrl,
      body.toString(),
      {headers: {'Content-Type': 'application/x-www-form-urlencoded'}},
    ).pipe(
      tap(response => {
        if (response.responseData?.playlist) {
          this.importPlaylists(response.responseData.playlist);
        }
      }),
      map(() => this.playlists),
    );
  }

  private importFromWindow(): void {
    const win = window as any;
    if (win.playlistsElementUrl) {
      this.playlistsElementUrl = win.playlistsElementUrl;
    }
    if (win.playlists) {
      this.importPlaylists(win.playlists);
    }
  }

  private importPlaylists(data: PlaylistDto[]): void {
    const existingIds = new Set(this.playlists.map(p => p.id));
    for (const item of data) {
      const id = Number(item.id);
      if (!existingIds.has(id)) {
        this.playlists.push({id, title: item.title, url: item.url});
        existingIds.add(id);
      }
    }
  }

  private extractPlaylistIds(data: PlaylistResponseData, elementId: number): number[] {
    if (data?.playlist) {
      this.importPlaylists(data.playlist);
    }
    const types: (keyof PlaylistResponseData)[] = ['zxMusic', 'zxPicture', 'zxProd', 'zxRelease'];
    for (const type of types) {
      const items = data?.[type] as PlaylistItemData[] | undefined;
      if (items) {
        for (const item of items) {
          if (Number(item.id) === elementId) {
            return (item.playlistIds || []).map(Number);
          }
        }
      }
    }
    return [];
  }

  private getRootUrl(): string {
    return `//${location.hostname}/`;
  }
}
