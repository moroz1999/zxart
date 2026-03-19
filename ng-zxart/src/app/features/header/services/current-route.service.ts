import {Injectable} from '@angular/core';

@Injectable({
  providedIn: 'root',
})
export class CurrentRouteService {
  get pathname(): string {
    return window.location.pathname;
  }

  get languageCode(): string {
    const parts = this.pathname.split('/').filter(Boolean);
    return parts[0] ?? 'eng';
  }

  isActive(url: string): boolean {
    try {
      const itemPath = new URL(url).pathname;
      return itemPath !== '/' && this.pathname.startsWith(itemPath);
    } catch {
      return false;
    }
  }
}
