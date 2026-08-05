import {ActivatedRoute, NavigationEnd, Router} from '@angular/router';
import {Observable} from 'rxjs';
import {distinctUntilChanged, filter, map, startWith} from 'rxjs/operators';

/**
 * Value of a route parameter that belongs to the child route of `route`, or
 * `null` while the page has no child route activated.
 *
 * A page whose optional trailing segment is declared as a child route keeps its
 * component alive when that segment appears or disappears, but the parameter
 * then lives on the child and never shows up in the page's own `paramMap`. The
 * child route also comes and goes, so the value is re-read after every
 * navigation rather than taken from a single `ActivatedRoute`.
 */
export function childRouteParam(route: ActivatedRoute, router: Router, name: string): Observable<string | null> {
  return router.events.pipe(
    filter((event): event is NavigationEnd => event instanceof NavigationEnd),
    startWith(null),
    map(() => route.firstChild?.snapshot.paramMap.get(name) ?? null),
    distinctUntilChanged(),
  );
}
