import {Inject, Injectable, InjectionToken} from '@angular/core';
import {DOCUMENT} from '@angular/common';
import {NavigationEnd, Router} from '@angular/router';
import {Observable} from 'rxjs';
import {distinctUntilChanged, filter, map, startWith} from 'rxjs/operators';
import {AssistantPage} from '../../../shared/models/assistant-data';

export const ASSISTANT_ORIGIN = new InjectionToken<string>('ASSISTANT_ORIGIN', {
  providedIn: 'root',
  factory: () => window.location.origin,
});

/** The first path segment of an entity page and the entity type the assistant calls it. */
const ENTITY_ROUTES: Readonly<Record<string, string>> = {
  'prod': 'prod',
  'release': 'release',
  'author': 'author',
  'author-alias': 'authorAlias',
  'group': 'group',
  'group-alias': 'groupAlias',
};

/**
 * The page the user writes from, sent along with every message: its URL, and
 * the entity when it is an entity page.
 */
@Injectable({providedIn: 'root'})
export class AssistantPageContextService {
  /** The page, again after every navigation. */
  readonly page$: Observable<AssistantPage>;

  /** The page's title, for the user to see which page goes along; again whenever it changes. */
  readonly title$: Observable<string>;

  constructor(
    private readonly router: Router,
    @Inject(ASSISTANT_ORIGIN) private readonly origin: string,
    @Inject(DOCUMENT) private readonly document: Document,
  ) {
    this.title$ = new Observable<void>(subscriber => {
      const observer = new MutationObserver(() => subscriber.next());
      observer.observe(this.document.head, {subtree: true, childList: true, characterData: true});
      return () => observer.disconnect();
    }).pipe(
      startWith(undefined),
      map(() => this.document.title),
      distinctUntilChanged(),
    );
    this.page$ = this.router.events.pipe(
      filter(event => event instanceof NavigationEnd),
      startWith(null),
      map(() => this.current()),
    );
  }

  current(): AssistantPage {
    const path = this.router.url;
    const page: AssistantPage = {url: this.origin + path};
    const match = /^\/([a-z-]+)\/(\d+)(?:[/?#]|$)/.exec(path);
    const entityType = match ? ENTITY_ROUTES[match[1]] : undefined;
    if (match && entityType) {
      page.entityType = entityType;
      page.entityId = Number(match[2]);
    }
    return page;
  }
}
