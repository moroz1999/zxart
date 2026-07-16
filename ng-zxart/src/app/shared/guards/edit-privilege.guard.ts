import {inject} from '@angular/core';
import {CanActivateFn, Router} from '@angular/router';
import {map} from 'rxjs/operators';
import {ElementPrivilegesApiService} from '../services/element-privileges-api.service';

/**
 * Route guard for entity form routes. Allows activation only if the current user
 * has the required privilege on the entity (route `data.privilege`); otherwise
 * redirects to the entity page (`/{data.entityPath}/{id}`). The backend enforces
 * the same privilege — this is the front-end gate so the form is never shown to
 * users who cannot use it.
 */
export const editPrivilegeGuard: CanActivateFn = route => {
  const api = inject(ElementPrivilegesApiService);
  const router = inject(Router);

  const id = Number(route.paramMap.get('id')) || 0;
  const privilege = route.data['privilege'] as string;
  const entityPath = route.data['entityPath'] as string;

  if (!id || !privilege || !entityPath) {
    return router.createUrlTree(['/']);
  }

  return api.getPrivileges(id, [privilege]).pipe(
    map(privileges =>
      privileges[privilege] === true ? true : router.createUrlTree([`/${entityPath}/${id}`]),
    ),
  );
};
