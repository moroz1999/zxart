import {ChangeDetectorRef} from '@angular/core';
import {describe, expect, it, vi} from 'vitest';
import {ZxToggleComponent} from './zx-toggle.component';

describe('ZxToggleComponent', () => {
  function createComponent(): ZxToggleComponent {
    const component = new ZxToggleComponent({markForCheck: () => {}} as ChangeDetectorRef);
    component.options = [{value: 'plain', label: 'Plain'}, {value: 'html', label: 'HTML'}];
    return component;
  }

  it('shows the value a form control writes without reporting a change', () => {
    const component = createComponent();
    const onChange = vi.fn();
    component.registerOnChange(onChange);

    component.writeValue('html');

    expect(component.value).toBe('html');
    expect(onChange).not.toHaveBeenCalled();
  });

  it('reports the option the user picks to the form control', () => {
    const component = createComponent();
    const onChange = vi.fn();
    const onTouched = vi.fn();
    component.registerOnChange(onChange);
    component.registerOnTouched(onTouched);

    component.onValueChange('html');

    expect(onChange).toHaveBeenCalledWith('html');
    expect(onTouched).toHaveBeenCalled();
  });

  it('ignores picks while disabled', () => {
    const component = createComponent();
    const onChange = vi.fn();
    component.registerOnChange(onChange);
    component.setDisabledState(true);

    component.onValueChange('html');

    expect(onChange).not.toHaveBeenCalled();
    expect(component.value).toBe('');
  });
});
