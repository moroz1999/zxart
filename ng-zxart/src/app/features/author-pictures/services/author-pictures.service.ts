import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';

@Injectable({
  providedIn: 'root'
})
export class AuthorPicturesService {
  constructor(private http: HttpClient) {}

  getPictures(elementId: number): Observable<ZxPictureDto[]> {
    return this.http.get<ZxPictureDto[]>('/pictures/', {
      params: {action: 'picturesByElement', elementId: String(elementId)},
    }).pipe(catchError(() => of([])));
  }
}
