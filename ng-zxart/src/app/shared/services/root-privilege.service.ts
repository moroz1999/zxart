import {Injectable} from '@angular/core';
import {combineLatest, Observable, of} from 'rxjs';
import {map, shareReplay, switchMap} from 'rxjs/operators';
import {CurrentUserService} from './current-user.service';
import {ElementPrivilegesApiService} from './element-privileges-api.service';

/**
 * Privileges resolved on the public root rather than on any one entity.
 *
 * `editHardware` is a privilege of its own because the hardware catalogue is a
 * plain table with no element behind it. Everything else is an ordinary element
 * action in `type.action` form, asked for on the root the same way the backend
 * asks for it: holding it there means holding it for the whole tree below.
 */
export const ROOT_PRIVILEGE_EDIT_HARDWARE = 'editHardware';
export const PRIVILEGE_CATEGORY_SAVE = 'zxProdCategory.receive';
export const PRIVILEGE_CATEGORY_DELETE = 'zxProdCategory.delete';
export const PRIVILEGE_COUNTRY_SAVE = 'country.receive';
export const PRIVILEGE_COUNTRY_DELETE = 'country.delete';
export const PRIVILEGE_CITY_DELETE = 'city.delete';

/**
 * The privileges that open some part of the management section. They are
 * granted together, but each one gates only its own screen, so anything
 * offering the section as a whole asks for all of them at once.
 */
export const ROOT_PRIVILEGES_MANAGE: readonly string[] = [
  ROOT_PRIVILEGE_EDIT_HARDWARE,
  PRIVILEGE_CATEGORY_SAVE,
  PRIVILEGE_COUNTRY_SAVE,
];

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

  /** Whether the user holds at least one of the privileges. */
  hasAny(privileges: readonly string[]): Observable<boolean> {
    return combineLatest(privileges.map(privilege => this.has(privilege))).pipe(
      map(results => results.includes(true)),
    );
  }
}
