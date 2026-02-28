import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';

@Injectable({
  providedIn: 'root'
})
export class MusicListService {
  constructor(private http: HttpClient) {}

  getTunes(elementId: number, compoType?: string): Observable<ZxTuneDto[]> {
    const params: Record<string, string> = {elementId: String(elementId)};
    if (compoType) {
      params['compoType'] = compoType;
    }
    return this.http.get<ZxTuneDto[]>('/musiclist/', {params}).pipe(
      catchError(() => of([]))
    );
  }
}
