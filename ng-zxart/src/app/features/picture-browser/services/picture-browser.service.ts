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

  getPaged(elementId: number, start: number, limit: number, sorting: string): Observable<PaginatedPicturesResponse> {
    return this.http.get<PaginatedPicturesResponse>('/picturelist/', {
      params: {elementId: String(elementId), start: String(start), limit: String(limit), sorting},
    }).pipe(
      catchError(() => of({total: 0, items: []}))
    );
  }
}
