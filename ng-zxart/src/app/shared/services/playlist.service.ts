import {Injectable} from '@angular/core';
import {HttpClient, HttpParams} from '@angular/common/http';
import {catchError, map, Observable, of} from 'rxjs';
import {JsonResponse} from '../models/json-response';
import {PlaylistItemData, PlaylistResponseData} from '../models/playlist-response-data';

/**
 * Membership of a single element in the current user's playlists. Every call
 * answers with the playlist ids the element now belongs to. The playlists
 * themselves are owned by {@link PlaylistsApiService}.
 */
@Injectable({
  providedIn: 'root',
})
export class PlaylistService {
  private readonly apiUrl = `//${location.hostname}/ajax/`;

  constructor(private http: HttpClient) {}

  fetchPlaylistIds(elementId: number): Observable<number[]> {
    const params = {id: elementId, action: 'getPlaylistIds'};
    return this.http.get<JsonResponse<PlaylistResponseData>>(this.apiUrl, {params: params as any}).pipe(
      map(response => this.extractPlaylistIds(response.responseData, elementId)),
      catchError(() => of([])),
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
      catchError(() => of([])),
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
      catchError(() => of([])),
    );
  }

  private extractPlaylistIds(data: PlaylistResponseData, elementId: number): number[] {
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
}
