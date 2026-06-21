import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {StatsCategorySection, StatsOverview, StatsUsersSection} from '../models/stats.models';

@Injectable({
  providedIn: 'root',
})
export class StatsService {
  readonly overview$: Observable<StatsOverview | null> = this.http
    .get<StatsOverview>('/stats/', {params: {action: 'overview'}})
    .pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );

  readonly soft$: Observable<StatsCategorySection | null> = this.getSection('soft');
  readonly music$: Observable<StatsCategorySection | null> = this.getSection('music');
  readonly gfx$: Observable<StatsCategorySection | null> = this.getSection('gfx');
  readonly users$: Observable<StatsUsersSection | null> = this.http
    .get<StatsUsersSection>('/stats/', {params: {action: 'users'}})
    .pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );

  constructor(private readonly http: HttpClient) {}

  private getSection(action: string): Observable<StatsCategorySection | null> {
    return this.http.get<StatsCategorySection>('/stats/', {params: {action}}).pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
  }
}
