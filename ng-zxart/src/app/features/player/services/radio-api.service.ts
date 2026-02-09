import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable} from 'rxjs';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {RadioCriteria} from '../models/radio-criteria';
import {RadioPreset} from '../models/radio-preset';
import {RadioFilterOptionsDto} from '../models/radio-filter-options';

interface ApiResponse<T> {
  responseStatus: string;
  responseData?: T;
  errorMessage?: string;
}

@Injectable({
  providedIn: 'root'
})
export class RadioApiService {
  private filterOptions$?: Observable<RadioFilterOptionsDto>;

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

  getFilterOptions(): Observable<RadioFilterOptionsDto> {
    if (!this.filterOptions$) {
      this.filterOptions$ = this.http
        .get<ApiResponse<RadioFilterOptionsDto>>('/radio/?action=options')
        .pipe(
          map(response => {
            if (response.responseStatus === 'success' && response.responseData) {
              return response.responseData;
            }
            throw new Error(response.errorMessage || 'Failed to load radio options');
          }),
        );
    }

    return this.filterOptions$;
  }
}
