import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {Observable, shareReplay} from 'rxjs';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {RadioCriteria} from '../models/radio-criteria';
import {RadioFilterOptionsDto} from '../models/radio-filter-options';

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

    return this.http.post<ZxTuneDto>('/radio/?action=next-tune', payload);
  }

  getFilterOptions(): Observable<RadioFilterOptionsDto> {
    if (!this.filterOptions$) {
      this.filterOptions$ = this.http
        .get<RadioFilterOptionsDto>('/radio/?action=options')
        .pipe(shareReplay(1));
    }

    return this.filterOptions$;
  }
}
