import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable} from 'rxjs';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {RadioCriteria} from '../models/radio-criteria';
import {RadioPreset} from '../models/radio-preset';

interface ApiResponse<T> {
  responseStatus: string;
  responseData?: T;
  errorMessage?: string;
}

@Injectable({
  providedIn: 'root'
})
export class RadioApiService {
  constructor(private http: HttpClient) {}

  getNextTune(criteria: RadioCriteria | null, preset: RadioPreset | null): Observable<ZxTuneDto> {
    const payload: {criteria?: RadioCriteria; preset?: RadioPreset} = {};
    if (criteria) {
      payload.criteria = criteria;
    }
    if (preset) {
      payload.preset = preset;
    }

    return this.http.post<ApiResponse<ZxTuneDto>>('/radio/?action=next-tune', payload).pipe(
      map(response => {
        if (response.responseStatus === 'success' && response.responseData) {
          return response.responseData;
        }
        throw new Error(response.errorMessage || 'Failed to load next tune');
      }),
    );
  }
}
