import {describe, expect, it} from 'vitest';
import {Tag} from '../models/tag';
import {tagToItem} from './tag-item';

describe('tagToItem', () => {
  it('names the item by the tag name, not by the decorated search title', () => {
    const tag = new Tag({
      id: 81401,
      url: '/tag/cover',
      title: 'Кавер, Копия, Копи (Музыка)',
      value: 'Кавер',
      synonym: 'Копия, Копи',
      description: 'Музыка',
    });

    expect(tagToItem(tag)).toEqual({id: 81401, title: 'Кавер', description: 'Музыка'});
  });

  it('leaves the description out when the tag has none', () => {
    const tag = new Tag({id: 5, url: '/tag/demo', title: 'Demo', value: 'Demo', synonym: '', description: ''});

    expect(tagToItem(tag)).toEqual({id: 5, title: 'Demo', description: null});
  });
});
