import {inject} from '@angular/core';
import {CanActivateChildFn} from '@angular/router';
import {map, take} from 'rxjs/operators';
import {CurrentUserService} from '../services/current-user.service';

/**
 * Resolves the current user before any route renders. This is the frontend
 * auto-login: `/currentuser/` triggers the backend to restore the session from
 * the remember cookie. Activation always proceeds — the guard only blocks the
 * first render until the user is known.
 */
export const authGuard: CanActivateChildFn = () => {
  const currentUserService = inject(CurrentUserService);
  return currentUserService.user$.pipe(take(1), map(() => true));
};
