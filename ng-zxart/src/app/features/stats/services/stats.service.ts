import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {
  StatsCategoryKey,
  StatsCategorySection,
  StatsCategorySummary,
  StatsDailySeries,
  StatsDistributionBlock,
  StatsDistributionsSection,
  StatsOverview,
  StatsTopUsersSection,
  StatsUsersSection,
  StatsYearSeries,
} from '../models/stats.models';

@Injectable({
  providedIn: 'root',
})
export class StatsService {
  private readonly cache = new Map<string, Observable<unknown | null>>();

  readonly overview$: Observable<StatsOverview | null> = this.getAction('overview');

  readonly soft$: Observable<StatsCategorySection | null> = this.getSection('soft');
  readonly music$: Observable<StatsCategorySection | null> = this.getSection('music');
  readonly gfx$: Observable<StatsCategorySection | null> = this.getSection('gfx');
  readonly users$: Observable<StatsUsersSection | null> = this.getAction('users');

  constructor(private readonly http: HttpClient) {}

  categorySummary(category: StatsCategoryKey): Observable<StatsCategorySummary | null> {
    return this.getAction(`${category}-summary`);
  }

  categorySeries(category: StatsCategoryKey): Observable<StatsYearSeries | null> {
    return this.getAction(`${category}-series`);
  }

  categoryDistributions(category: StatsCategoryKey): Observable<StatsDistributionsSection | null> {
    return this.getAction(`${category}-distributions`);
  }

  distributionBlock(action: string): Observable<StatsDistributionBlock | null> {
    return this.getAction(action);
  }

  categoryDaily(category: StatsCategoryKey): Observable<StatsDailySeries | null> {
    return this.getAction(`${category}-daily`);
  }

  categoryTop(category: StatsCategoryKey): Observable<StatsTopUsersSection | null> {
    return this.getAction(`${category}-top`);
  }

  usersTop(kind: 'voters' | 'comments' | 'tags'): Observable<StatsTopUsersSection | null> {
    return this.getAction(`users-${kind}`);
  }

  private getSection(action: string): Observable<StatsCategorySection | null> {
    return this.getAction(action);
  }

  private getAction<T>(action: string): Observable<T | null> {
    const cached = this.cache.get(action);
    if (cached) {
      return cached as Observable<T | null>;
    }

    const request$ = this.http.get<T>('/stats/', {params: {action}}).pipe(
      catchError(() => of(null)),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.cache.set(action, request$);

    return request$;
  }
}
