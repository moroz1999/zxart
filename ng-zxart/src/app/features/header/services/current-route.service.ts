import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class CurrentRouteService {
  get pathname(): string {
    return window.location.pathname;
  }

  isActive(url: string): boolean {
    try {
      // Menu URLs are relative SPA paths (e.g. `/prods`); resolve against the
      // current origin so `new URL()` doesn't throw on the missing base.
      const itemPath = new URL(url, window.location.origin).pathname;
      return itemPath !== '/' && this.pathname.startsWith(itemPath);
    } catch {
      return false;
    }
  }
}
