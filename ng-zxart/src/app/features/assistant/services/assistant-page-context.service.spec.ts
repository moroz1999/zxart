import {Router} from '@angular/router';
import {describe, expect, it} from 'vitest';
import {AssistantPageContextService} from './assistant-page-context.service';

function contextAt(url: string): AssistantPageContextService {
  return new AssistantPageContextService({url} as Router, 'https://zxart.ee');
}

describe('AssistantPageContextService', () => {
  it('names the entity of an entity page', () => {
    expect(contextAt('/author-alias/42/prods').current()).toEqual({
      url: 'https://zxart.ee/author-alias/42/prods',
      entityType: 'authorAlias',
      entityId: 42,
    });
  });

  it('sends only the url of a page without an entity', () => {
    expect(contextAt('/prods?page=2').current()).toEqual({url: 'https://zxart.ee/prods?page=2'});
  });
});
