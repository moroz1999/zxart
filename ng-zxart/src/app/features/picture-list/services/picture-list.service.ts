import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

export interface RelatedPicturesResponse {
  type: 'game' | 'authors' | 'none';
  items: ZxPictureDto[];
}

@Injectable({
  providedIn: 'root'
})
export class PictureListService {
  constructor(private http: HttpClient) {}

  getPictures(elementId: number, compoType?: string): Observable<ZxPictureDto[]> {
    const params: Record<string, string> = {elementId: String(elementId)};
    if (compoType) {
      params['compoType'] = compoType;
    }
    return this.http.get<ZxPictureDto[]>('/picturelist/', {params}).pipe(
      catchError(() => of([]))
    );
  }

  getRelated(pictureId: number): Observable<RelatedPicturesResponse> {
    return this.http.get<RelatedPicturesResponse>('/picturelist/', {
      params: {action: 'related', pictureId: String(pictureId)},
    }).pipe(
      catchError(() => of({type: 'none' as const, items: []}))
    );
  }
}
