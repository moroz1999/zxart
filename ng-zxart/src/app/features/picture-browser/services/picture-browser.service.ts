import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

export interface PaginatedPicturesResponse {
  total: number;
  items: ZxPictureDto[];
}

@Injectable({
  providedIn: 'root',
})
export class PictureBrowserService {
  constructor(private http: HttpClient) {}

  /** `tagId` 0 browses the whole picture collection; a tag id narrows it to that tag. */
  getPaged(tagId: number, start: number, limit: number, sorting: string): Observable<PaginatedPicturesResponse> {
    return this.http.get<PaginatedPicturesResponse>('/picturelist/', {
      params: {tagId: String(tagId), start: String(start), limit: String(limit), sorting},
    }).pipe(
      catchError(() => of({total: 0, items: []}))
    );
  }
}
