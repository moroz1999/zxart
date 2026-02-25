import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';
import {HttpClient} from '@angular/common/http';
import {catchError, Observable, of, shareReplay} from 'rxjs';

interface LegacyCurrentUser {
  userName?: string;
  id?: number | string;
}

@Injectable({
  providedIn: 'root'
})
export class CurrentUserService {
  private readonly isBrowser: boolean;
  private user: LegacyCurrentUser | null = null;
  private readonly userRequest$: Observable<LegacyCurrentUser | null>;

  constructor(
    @Inject(PLATFORM_ID) platformId: object,
    private http: HttpClient,
  ) {
    this.isBrowser = isPlatformBrowser(platformId);
    this.userRequest$ = this.isBrowser ? this.fetchUser().pipe(shareReplay(1)) : of(null);
    this.userRequest$.subscribe((user) => {
      this.user = user;
    });
  }

  loadUser(): Observable<LegacyCurrentUser | null> {
    return this.userRequest$;
  }

  private fetchUser(): Observable<LegacyCurrentUser | null> {
    return this.http.get<LegacyCurrentUser>('/currentuser/').pipe(
      catchError(() => of(null)),
    );
  }

  get isAuthenticated(): boolean {
    if (!this.isBrowser) {
      return false;
    }
    const user = this.user;
    return !!user && user.userName !== 'anonymous';
  }

  get userId(): number | null {
    if (!this.isBrowser) {
      return null;
    }
    const user = this.user;
    if (!user || user.id === undefined || user.id === null) {
      return null;
    }
    const parsed = Number(user.id);
    return Number.isFinite(parsed) ? parsed : null;
  }
}
