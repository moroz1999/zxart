import {Injectable} from '@angular/core';
import {HttpClient} from '@angular/common/http';
import {BehaviorSubject, catchError, defer, Observable, of} from 'rxjs';
import {filter, map, tap} from 'rxjs/operators';
import {CurrentUser} from '../models/current-user';

const ANONYMOUS_USER: CurrentUser = {
  id: null,
  userName: 'anonymous',
  hasAds: true,
  authorPageUrl: null,
};

@Injectable({
  providedIn: 'root',
})
export class CurrentUserService {
  private readonly apiUrl = '/currentuser/';
  private readonly store = new BehaviorSubject<CurrentUser | null>(null);
  private loading = false;

  readonly user$: Observable<CurrentUser> = defer(() => {
    if (this.store.getValue() === null && !this.loading) {
      this.loadCurrentUser();
    }
    return this.store.pipe(filter((user): user is CurrentUser => user !== null));
  });

  readonly isAuthenticated$: Observable<boolean> = this.user$.pipe(
    map(user => user.userName !== 'anonymous'),
  );

  readonly userId$: Observable<number | null> = this.user$.pipe(
    map(user => user.id),
  );

  constructor(private http: HttpClient) {}

  private loadCurrentUser(): void {
    this.loading = true;
    this.http.get<CurrentUser>(this.apiUrl).pipe(
      catchError(() => of(ANONYMOUS_USER)),
    ).subscribe(user => {
      this.loading = false;
      this.store.next(user);
    });
  }

  login(userName: string, password: string, remember: boolean): Observable<CurrentUser> {
    return this.http.post<CurrentUser>(`${this.apiUrl}?action=login`, {userName, password, remember}).pipe(
      tap(user => this.store.next(user)),
    );
  }

  logout(): Observable<CurrentUser> {
    return this.http.post<CurrentUser>(`${this.apiUrl}?action=logout`, {}).pipe(
      tap(user => this.store.next(user)),
    );
  }
}
