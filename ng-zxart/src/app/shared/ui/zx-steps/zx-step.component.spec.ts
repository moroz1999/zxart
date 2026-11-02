import {describe, expect, it} from 'vitest';
import {ZxStepComponent} from './zx-step.component';

describe('ZxStepComponent', () => {
  it('shows its number until it has a status of its own', () => {
    const component = new ZxStepComponent();
    component.number = 3;

    expect(component.hostClass.split(' ')).toEqual(['zx-step', 'zx-step--number']);
    expect(component.markerText).toBe('3');
  });

  it('marks its status on the host', () => {
    const component = new ZxStepComponent();
    component.status = 'failed';

    expect(component.hostClass.split(' ')).toContain('zx-step--failed');
  });

  it('steps back while it waits', () => {
    const component = new ZxStepComponent();
    component.status = 'pending';

    expect(component.hostClass.split(' ')).toContain('zx-step--pending');
  });
});
