import {Injectable} from '@angular/core';
import {environment} from '../../../environments/environment';

@Injectable({
  providedIn: 'root',
})
export class LocalStorageService {
  private readonly available: boolean;
  private readonly prefix = `zx-${environment.storageVersion}-`;

  constructor() {
    this.available = this.checkAvailability();
  }

  get<T>(key: string): T | null {
    if (!this.available) {
      return null;
    }
    try {
      const raw = localStorage.getItem(this.prefix + key);
      if (raw === null) {
        return null;
      }
      return JSON.parse(raw) as T;
    } catch {
      return null;
    }
  }

  set(key: string, value: unknown): void {
    if (!this.available) {
      return;
    }
    try {
      localStorage.setItem(this.prefix + key, JSON.stringify(value));
    } catch {
      // quota exceeded or storage blocked
    }
  }

  remove(key: string): void {
    if (!this.available) {
      return;
    }
    try {
      localStorage.removeItem(this.prefix + key);
    } catch {
      // ignore
    }
  }

  /** Returns the full prefixed key as stored in localStorage. Useful for StorageEvent.key comparisons. */
  getKey(key: string): string {
    return this.prefix + key;
  }

  private checkAvailability(): boolean {
    try {
      const testKey = '__zx_storage_test__';
      localStorage.setItem(testKey, '1');
      localStorage.removeItem(testKey);
      return true;
    } catch {
      return false;
    }
  }
}
