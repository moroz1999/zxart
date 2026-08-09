import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class TunePlayService {
  constructor(private http: HttpClient) {}

  logPlay(tuneId: number, context: string | null): Observable<void> {
    const payload: {tuneId: number; context?: string} = {tuneId};
    if (context) {
      payload.context = context;
    }

    return this.http.post<{success: true}>('/tunes/?action=play', payload).pipe(
      map(() => undefined),
    );
  }
}
