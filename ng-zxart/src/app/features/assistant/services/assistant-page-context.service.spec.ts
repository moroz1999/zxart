import {NavigationEnd, Router} from '@angular/router';
import {firstValueFrom, Subject} from 'rxjs';
import {take, toArray} from 'rxjs/operators';
import {describe, expect, it} from 'vitest';
import {AssistantPageContextService} from './assistant-page-context.service';

function contextAt(url: string, events = new Subject<unknown>()): {service: AssistantPageContextService; router: {url: string}} {
  const router = {url, events};
  return {service: new AssistantPageContextService(router as unknown as Router, 'https://zxart.ee'), router};
}

describe('AssistantPageContextService', () => {
  it('names the entity of an entity page', () => {
    expect(contextAt('/author-alias/42/prods').service.current()).toEqual({
      url: 'https://zxart.ee/author-alias/42/prods',
      entityType: 'authorAlias',
      entityId: 42,
    });
  });

  it('sends only the url of a page without an entity', () => {
    expect(contextAt('/prods?page=2').service.current()).toEqual({url: 'https://zxart.ee/prods?page=2'});
  });

  it('follows the navigation', async () => {
    const events = new Subject<unknown>();
    const {service, router} = contextAt('/prods', events);
    const pages = firstValueFrom(service.page$.pipe(take(2), toArray()));

    router.url = '/prod/7';
    events.next(new NavigationEnd(1, '/prod/7', '/prod/7'));

    expect(await pages).toEqual([
      {url: 'https://zxart.ee/prods'},
      {url: 'https://zxart.ee/prod/7', entityType: 'prod', entityId: 7},
    ]);
  });
});
