import {DOCUMENT} from '@angular/common';
import {Inject, Injectable} from '@angular/core';

type YmFunction = {
  (id: number, action: 'init', options: Record<string, unknown>): void;
  (id: number, action: 'hit', url: string, options: Record<string, unknown>): void;
  (id: number, action: 'reachGoal', goal: string, params: Record<string, unknown>, callback: () => void): void;
  a?: unknown[][];
  l?: number;
};

@Injectable({providedIn: 'root'})
export class AnalyticsService {
  private readonly metrikaId = 94686067;
  private initialized = false;
  private previousPageUrl: string | null = null;

  constructor(
    @Inject(DOCUMENT) private readonly document: Document,
  ) {}

  initialize(): void {
    if (this.initialized) {
      return;
    }
    this.initialized = true;

    const windowWithMetrika = window as typeof window & {ym?: YmFunction};
    if (!windowWithMetrika.ym) {
      const queue: YmFunction = ((...args: unknown[]) => {
        queue.a = queue.a ?? [];
        queue.a.push(args);
      }) as YmFunction;
      queue.l = Date.now();
      windowWithMetrika.ym = queue;
    }

    const script = this.document.createElement('script');
    script.async = true;
    script.src = `https://mc.yandex.ru/metrika/tag.js?id=${this.metrikaId}`;
    this.document.head.appendChild(script);

    windowWithMetrika.ym(this.metrikaId, 'init', {
      ssr: true,
      defer: true,
      clickmap: false,
      trackLinks: true,
      accurateTrackBounce: true,
    });
  }

  reachGoal(goal: string, params?: Record<string, unknown>, callback?: () => void): void {
    this.getYm()?.(
      this.metrikaId,
      'reachGoal',
      goal,
      params ?? {},
      callback ?? (() => undefined),
    );
  }

  trackPageView(url: string): void {
    const absoluteUrl = new URL(url, this.document.location.origin).href;
    this.getYm()?.(this.metrikaId, 'hit', url, {
      title: this.document.title,
      referer: this.previousPageUrl ?? this.document.referrer,
    });
    this.previousPageUrl = absoluteUrl;
  }

  private getYm(): YmFunction | undefined {
    return (window as typeof window & {ym?: YmFunction}).ym;
  }
}
