import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {map, Observable, shareReplay} from 'rxjs';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {RadioCriteria} from '../models/radio-criteria';
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

  getNextTune(criteria: RadioCriteria | null): Observable<ZxTuneDto> {
    const payload: {criteria?: RadioCriteria} = {};
    if (criteria) {
      payload.criteria = criteria;
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
          shareReplay(1),
        );
    }

    return this.filterOptions$;
  }
}
