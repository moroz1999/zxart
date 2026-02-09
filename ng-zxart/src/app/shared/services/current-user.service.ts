import {Inject, Injectable, PLATFORM_ID} from '@angular/core';
import {isPlatformBrowser} from '@angular/common';

interface LegacyCurrentUser {
  userName?: string;
  id?: number | string;
}

@Injectable({
  providedIn: 'root'
})
export class CurrentUserService {
  private readonly isBrowser: boolean;

  constructor(@Inject(PLATFORM_ID) platformId: object) {
    this.isBrowser = isPlatformBrowser(platformId);
  }

  get isAuthenticated(): boolean {
    if (!this.isBrowser) {
      return false;
    }
    const user = (window as {currentUser?: LegacyCurrentUser}).currentUser;
    return !!user && user.userName !== 'anonymous';
  }

  get userId(): number | null {
    if (!this.isBrowser) {
      return null;
    }
    const user = (window as {currentUser?: LegacyCurrentUser}).currentUser;
    if (!user || user.id === undefined || user.id === null) {
      return null;
    }
    const parsed = Number(user.id);
    return Number.isFinite(parsed) ? parsed : null;
  }
}
