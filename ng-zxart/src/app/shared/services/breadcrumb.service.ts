import {Injectable} from '@angular/core';
import {Location} from '@angular/common';
import {ActivatedRouteSnapshot, NavigationEnd, PRIMARY_OUTLET, Router} from '@angular/router';
import {TranslateService} from '@ngx-translate/core';
import {EMPTY, merge, Observable, Subject} from 'rxjs';
import {filter, shareReplay, startWith, switchMap} from 'rxjs/operators';
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

/**
 * The trail plus whether it is still being resolved. While `loading` is true
 * the bar renders its skeleton, so the space the trail will take is reserved
 * from the first frame and the page below it never moves.
 */
export interface BreadcrumbState extends BreadcrumbTrail {
  loading: boolean;
}

const LOADING_STATE: BreadcrumbState = {items: [], currentTitle: '', loading: true};
const EMPTY_STATE: BreadcrumbState = {items: [], currentTitle: '', loading: false};

/**
 * Owns the single breadcrumb trail rendered by `zx-breadcrumb-bar` in the shell,
 * above every page's `<h1>`. Mirrors {@link PageMetadataService}: a route-driven
 * default (built from the top menu structure) is overridden by entity detail
 * pages, which push their own richer trail (section → category path → title) via
 * {@link setEntityTrail}.
 *
 * Route-driven pages carry a `titleKey` in their route data; pages without one
 * are entity/detail pages, whose trail is only known once the entity has loaded.
 * Those report `loading` until they publish a trail (or {@link setNotFoundTrail}
 * for an entity that does not exist), and the route stream stays silent while
 * the page stays on the same entity, so a pushed trail survives in-page
 * navigation (e.g. switching tabs).
 */
@Injectable({providedIn: 'root'})
export class BreadcrumbService {
  private readonly entityTrail = new Subject<BreadcrumbState>();

  /** Entity the trail on screen belongs to; see {@link entityKeyOf}. */
  private entityKey: string | null = null;

  private readonly routeState$: Observable<BreadcrumbState> = merge(
    this.router.events.pipe(filter((event): event is NavigationEnd => event instanceof NavigationEnd)),
    this.translate.onLangChange,
  ).pipe(
    startWith(null),
    switchMap(() => {
      const state = this.buildRouteState();
      return state ? [state] : EMPTY;
    }),
  );

  readonly state$: Observable<BreadcrumbState> = merge(this.routeState$, this.entityTrail).pipe(
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly router: Router,
    private readonly translate: TranslateService,
    private readonly location: Location,
  ) {}

  /** Overrides the trail for the current page (entity detail views). */
  setEntityTrail(trail: BreadcrumbTrail): void {
    this.entityTrail.next({...trail, loading: false});
  }

  /** Trail of an entity page whose entity does not exist; ends the skeleton. */
  setNotFoundTrail(): void {
    this.entityTrail.next({
      items: [],
      currentTitle: this.translate.instant('common.not-found'),
      loading: false,
    });
  }

  private buildRouteState(): BreadcrumbState | null {
    const root = this.router.routerState.snapshot.root;
    if (!root.firstChild) {
      // The first navigation has not resolved yet, so only the browser URL is
      // known: every page but home shows a trail and needs its space reserved.
      return this.isHomePath(this.location.path()) ? EMPTY_STATE : LOADING_STATE;
    }

    const tree = this.router.parseUrl(this.router.url);
    const group = tree.root.children[PRIMARY_OUTLET];
    const path = group ? '/' + group.segments.map(segment => segment.path).join('/') : '/';

    if (path === '/') {
      this.entityKey = null;
      return EMPTY_STATE; // home has no breadcrumbs; clears any prior trail
    }

    const titleKey = this.deepestTitleKey(root);
    if (!titleKey) {
      // Entity detail routes (…/:id) publish their own trail. It belongs to the
      // entity, not to the URL: it stays while the reader moves around inside
      // the page (in-page tabs, query parameters) and starts over as a skeleton
      // once another entity opens. A route without a usable id addresses no
      // entity, so nothing will ever publish a trail for it.
      const entityKey = this.entityKeyOf(path, root);
      const sameEntity = entityKey === this.entityKey;
      this.entityKey = entityKey;

      if (entityKey === null) {
        return EMPTY_STATE;
      }
      return sameEntity ? null : LOADING_STATE;
    }

    this.entityKey = null;

    const menuTrail = findMenuTrail(path, tree.queryParams as Record<string, string>);
    if (menuTrail) {
      const items = menuTrail.slice(0, -1).map(entry => this.toItem(entry));
      return {items, currentTitle: this.label(menuTrail[menuTrail.length - 1]), loading: false};
    }

    return {items: [], currentTitle: this.translate.instant(titleKey), loading: false};
  }

  private isHomePath(path: string): boolean {
    const clean = path.split('?')[0].split('#')[0];
    return clean === '' || clean === '/';
  }

  private deepestTitleKey(route: ActivatedRouteSnapshot): string | undefined {
    let found = route.data['titleKey'] as string | undefined;
    if (route.firstChild) {
      found = this.deepestTitleKey(route.firstChild) ?? found;
    }
    return found;
  }

  /**
   * The entity the current page is about, as `<section>/<id>` (`prod/589898`),
   * or `null` when the route names no existing entity. The trailing segments a
   * page spends on its own tabs are not part of it.
   */
  private entityKeyOf(path: string, route: ActivatedRouteSnapshot): string | null {
    const id = Number(this.deepestIdParam(route));
    return Number.isInteger(id) && id > 0 ? `${path.split('/')[1]}/${id}` : null;
  }

  private deepestIdParam(route: ActivatedRouteSnapshot): string | null {
    const child = route.firstChild ? this.deepestIdParam(route.firstChild) : null;
    return child ?? route.paramMap.get('id');
  }

  private toItem(entry: MenuEntry): BreadcrumbItemDto {
    return {title: this.label(entry), url: entry.url, queryParams: entry.queryParams};
  }

  private label(entry: MenuEntry): string {
    return entry.labelKey ? this.translate.instant(entry.labelKey) : entry.label ?? '';
  }
}
