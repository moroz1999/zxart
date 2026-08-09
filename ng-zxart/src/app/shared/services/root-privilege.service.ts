import {Injectable} from '@angular/core';
import {Observable, of} from 'rxjs';
import {map, shareReplay, switchMap} from 'rxjs/operators';
import {CurrentUserService} from './current-user.service';
import {ElementPrivilegesApiService} from './element-privileges-api.service';

/** Site-wide privileges, held on the public root rather than on any entity. */
export const ROOT_PRIVILEGE_EDIT_HARDWARE = 'editHardware';

/**
 * Answers whether the current user holds a site-wide privilege.
 *
 * These are stored on the public root element, whose id comes from
 * `/currentuser/` — the SPA never hardcodes it. Each privilege is asked for
 * once per session and the answer is replayed, so a nav entry and a route guard
 * checking the same one cost a single request.
 */
@Injectable({
  providedIn: 'root',
})
export class RootPrivilegeService {
  private readonly cache = new Map<string, Observable<boolean>>();

  constructor(
    private readonly currentUser: CurrentUserService,
    private readonly privileges: ElementPrivilegesApiService,
  ) {}

  has(privilege: string): Observable<boolean> {
    const cached = this.cache.get(privilege);
    if (cached) {
      return cached;
    }

    const result$ = this.currentUser.user$.pipe(
      switchMap(user => {
        // an anonymous visitor can hold nothing; skip the request entirely
        if (user.userName === 'anonymous' || user.publicRootId <= 0) {
          return of(false);
        }
        return this.privileges
          .getPrivileges(user.publicRootId, [privilege])
          .pipe(map(privileges => privileges[privilege] === true));
      }),
      shareReplay({bufferSize: 1, refCount: false}),
    );
    this.cache.set(privilege, result$);

    return result$;
  }
}
