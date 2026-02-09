import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable} from 'rxjs';

interface ApiResponse<T> {
  responseStatus: string;
  responseData?: T;
  errorMessage?: string;
}

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

    return this.http.post<ApiResponse<void>>('/tunes/?action=play', payload).pipe(
      map(response => {
        if (response.responseStatus === 'success') {
          return;
        }
        throw new Error(response.errorMessage || 'Failed to log play');
      }),
    );
  }
}
