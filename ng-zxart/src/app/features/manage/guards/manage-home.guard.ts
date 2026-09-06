import {inject} from '@angular/core';
import {CanActivateFn, Router} from '@angular/router';
import {combineLatest} from 'rxjs';
import {map} from 'rxjs/operators';
import {RootPrivilegeService} from '../../../shared/services/root-privilege.service';
import {MANAGE_SECTIONS} from '../manage-sections';

/**
 * Sends `/manage` to the first section the current user may actually open.
 *
 * A fixed redirect would land a user who holds only one of the section
 * privileges on a screen its own guard then bounces them off; a user who holds
 * none goes home.
 */
export const manageHomeGuard: CanActivateFn = () => {
  const privileges = inject(RootPrivilegeService);
  const router = inject(Router);

  return combineLatest(MANAGE_SECTIONS.map(section => privileges.has(section.privilege))).pipe(
    map(allowed => {
      const section = MANAGE_SECTIONS.find((_, index) => allowed[index]);
      return router.parseUrl(section?.href ?? '/');
    }),
  );
};
