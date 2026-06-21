import {HttpClient} from '@angular/common/http';
import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {catchError, shareReplay} from 'rxjs/operators';
import {StatsCategoryKey, StatsCategorySection, StatsOverview, StatsUsersSection} from '../models/stats.models';

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

  private readonly categoryCache = new Map<StatsCategoryKey, Observable<StatsCategorySection | null>>();
  private usersSection$?: Observable<StatsUsersSection | null>;

  constructor(private readonly http: HttpClient) {}

  getCategory(key: StatsCategoryKey): Observable<StatsCategorySection | null> {
    let section$ = this.categoryCache.get(key);
    if (!section$) {
      section$ = this.http.get<StatsCategorySection>('/stats/', {params: {action: key}}).pipe(
        catchError(() => of(null)),
        shareReplay({bufferSize: 1, refCount: false}),
      );
      this.categoryCache.set(key, section$);
    }

    return section$;
  }

  getUsers(): Observable<StatsUsersSection | null> {
    if (!this.usersSection$) {
      this.usersSection$ = this.http.get<StatsUsersSection>('/stats/', {params: {action: 'users'}}).pipe(
        catchError(() => of(null)),
        shareReplay({bufferSize: 1, refCount: false}),
      );
    }

    return this.usersSection$;
  }
}
