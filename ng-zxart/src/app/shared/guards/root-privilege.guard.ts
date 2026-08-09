import {inject} from '@angular/core';
import {CanActivateFn, Router} from '@angular/router';
import {map} from 'rxjs/operators';
import {RootPrivilegeService} from '../services/root-privilege.service';

/**
 * Route guard for site-wide management screens. Allows activation only if the
 * current user holds the route's `data.privilege` on the public root; otherwise
 * sends them home. The backend enforces the same privilege — this only keeps
 * the screen out of sight of users who cannot use it.
 */
export const rootPrivilegeGuard: CanActivateFn = route => {
  const privileges = inject(RootPrivilegeService);
  const router = inject(Router);

  const privilege = route.data['privilege'] as string | undefined;
  if (!privilege) {
    return router.createUrlTree(['/']);
  }

  return privileges.has(privilege).pipe(
    map(allowed => allowed === true ? true : router.createUrlTree(['/'])),
  );
};
