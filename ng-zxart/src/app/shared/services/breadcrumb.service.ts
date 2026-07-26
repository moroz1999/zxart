import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, NavigationEnd, PRIMARY_OUTLET, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {EMPTY, merge, Observable, Subject} from 'rxjs';
import {filter, map, startWith, switchMap} from 'rxjs/operators';
import {shareReplay} from 'rxjs/operators';
import {BreadcrumbItemDto} from '../ui/zx-breadcrumbs/zx-breadcrumbs.component';
import {findMenuTrail} from '../navigation/menu-trail';
import {MenuEntry} from '../navigation/menu.config';

/**
 * A resolved breadcrumb trail: the linked ancestor `items` (root-first, never
 * including the site root "ZX-Art", which `zx-breadcrumbs` always renders) plus
 * the non-linked `currentTitle` for the current page.
 */
export interface BreadcrumbTrail {
  items: BreadcrumbItemDto[];
  currentTitle: string;
}

const EMPTY_TRAIL: BreadcrumbTrail = {items: [], currentTitle: ''};

/**
 * Owns the single breadcrumb trail rendered by `zx-breadcrumb-bar` in the shell,
 * above every page's `<h1>`. Mirrors {@link PageMetadataService}: a route-driven
 * default (built from the top menu structure) is overridden by entity detail
 * pages, which push their own richer trail (section → category path → title) via
 * {@link setEntityTrail}.
 *
 * Route-driven pages carry a `titleKey` in their route data; pages without one
 * are entity/detail pages, for which the route stream stays silent so a pushed
 * entity trail survives in-page navigation (e.g. switching tabs).
 */
@Injectable({providedIn: 'root'})
export class BreadcrumbService {
  private readonly entityTrail = new Subject<BreadcrumbTrail>();

  private readonly routeTrail$: Observable<BreadcrumbTrail> = merge(
    this.router.events.pipe(filter((event): event is NavigationEnd => event instanceof NavigationEnd)),
    this.translate.onLangChange,
  ).pipe(
    startWith(null),
    switchMap(() => {
      const trail = this.buildRouteTrail();
      return trail ? [trail] : EMPTY;
    }),
  );

  readonly trail$: Observable<BreadcrumbTrail> = merge(this.routeTrail$, this.entityTrail).pipe(
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly router: Router,
    private readonly translate: TranslateService,
  ) {}

  /** Overrides the trail for the current page (entity detail views). */
  setEntityTrail(trail: BreadcrumbTrail): void {
    this.entityTrail.next(trail);
  }

  private buildRouteTrail(): BreadcrumbTrail | null {
    const tree = this.router.parseUrl(this.router.url);
    const group = tree.root.children[PRIMARY_OUTLET];
    const path = group ? '/' + group.segments.map(segment => segment.path).join('/') : '/';

    if (path === '/') {
      return EMPTY_TRAIL; // home has no breadcrumbs; clears any prior trail
    }

    const root = this.router.routerState.snapshot.root;
    const titleKey = this.deepestTitleKey(root);
    if (!titleKey) {
      // Entity detail routes (…/:id) publish their own trail and must keep it
      // across in-page navigation (tab switches), so stay silent for them;
      // any other trail-less route clears the bar.
      return this.hasIdParam(root) ? null : EMPTY_TRAIL;
    }

    const menuTrail = findMenuTrail(path, tree.queryParams as Record<string, string>);
    if (menuTrail) {
      const items = menuTrail.slice(0, -1).map(entry => this.toItem(entry));
      return {items, currentTitle: this.label(menuTrail[menuTrail.length - 1])};
    }

    return {items: [], currentTitle: this.translate.instant(titleKey)};
  }

  private deepestTitleKey(route: ActivatedRouteSnapshot): string | undefined {
    let found = route.data['titleKey'] as string | undefined;
    if (route.firstChild) {
      found = this.deepestTitleKey(route.firstChild) ?? found;
    }
    return found;
  }

  private hasIdParam(route: ActivatedRouteSnapshot): boolean {
    return route.paramMap.has('id') || (route.firstChild ? this.hasIdParam(route.firstChild) : false);
  }

  private toItem(entry: MenuEntry): BreadcrumbItemDto {
    return {title: this.label(entry), url: entry.url, queryParams: entry.queryParams};
  }

  private label(entry: MenuEntry): string {
    return entry.labelKey ? this.translate.instant(entry.labelKey) : entry.label ?? '';
  }
}
