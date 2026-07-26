import {inject, Injectable} from '@angular/core';
import {PRIMARY_OUTLET, Router, UrlTree} from '@angular/router';

/**
 * Active-navigation state for the hand-built menus. The menu is a flat config
 * rather than a set of `routerLink` anchors, so `routerLinkActive` cannot be
 * used and the check is made against the router's own URL.
 */
@Injectable({
  providedIn: 'root',
})
export class CurrentRouteService {
  private readonly router = inject(Router);

  get pathname(): string {
    return this.toPath(this.router.parseUrl(this.router.url));
  }

  isActive(url: string): boolean {
    const itemPath = this.toPath(this.router.parseUrl(url));
    return itemPath !== '/' && this.pathname.startsWith(itemPath);
  }

  private toPath(tree: UrlTree): string {
    const group = tree.root.children[PRIMARY_OUTLET];
    return group ? '/' + group.segments.map(segment => segment.path).join('/') : '/';
  }
}
