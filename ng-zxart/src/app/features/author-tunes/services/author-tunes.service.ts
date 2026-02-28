import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

@Injectable({
  providedIn: 'root'
})
export class AuthorTunesService {
  constructor(private http: HttpClient) {}

  getTunes(elementId: number): Observable<ZxTuneDto[]> {
    return this.http.get<ZxTuneDto[]>('/tunes/', {
      params: {action: 'tunesByElement', elementId: String(elementId)},
    }).pipe(catchError(() => of([])));
  }
}
