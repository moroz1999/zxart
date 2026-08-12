import {inject} from '@angular/core';
import {ResolveFn} from '@angular/router';
import {EntityPrefetchService} from '../services/entity-prefetch.service';

/**
 * Builds a resolver that starts an entity request as the navigation begins,
 * without holding the navigation up: it returns immediately, so the request
 * travels while the router still downloads the page's chunk.
 *
 * Resolvers run after the guards, so the interface language is already the one
 * the visitor's account asks for and the prefetched response is localized the
 * same way the page's own request would have been.
 */
export function prefetchEntityResolver(urls: readonly string[], paramName: string): ResolveFn<boolean> {
  return route => {
    const id = route.paramMap.get(paramName);
    if (id) {
      inject(EntityPrefetchService).prefetch(urls, {id});
    }
    return true;
  };
}
