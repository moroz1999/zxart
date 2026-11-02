import {describe, expect, it} from 'vitest';
import {ZxListItemComponent} from './zx-list-item.component';

describe('ZxListItemComponent', () => {
  it('is a plain row by default', () => {
    const component = new ZxListItemComponent();

    expect(component.hostClass).toBe('zx-list-item');
  });

  it('marks the active row', () => {
    const component = new ZxListItemComponent();
    component.active = true;

    expect(component.hostClass.split(' ')).toContain('zx-list-item--active');
  });
});
