import {describe, expect, it} from 'vitest';
import {ZxPanelComponent} from './zx-panel.component';

describe('ZxPanelComponent', () => {
  it('keeps the plain panel background by default', () => {
    const component = new ZxPanelComponent();

    expect(component.hostClass.split(' ')).toContain('zx-panel--surface-default');
  });

  it('marks a tinted surface on the host', () => {
    const component = new ZxPanelComponent();
    component.variant = 'flat';
    component.surface = 'primary';

    expect(component.hostClass.split(' ')).toEqual(expect.arrayContaining(['zx-panel--flat', 'zx-panel--surface-primary']));
  });
});
