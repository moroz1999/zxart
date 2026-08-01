import {ChangeDetectorRef} from '@angular/core';
import {ZxSelectComponent, ZxSelectOption} from './zx-select.component';

describe('ZxSelectComponent', () => {
  const options: ZxSelectOption[] = [
    {value: '0', label: '2026'},
    {value: '1', label: '2025'},
  ];

  function createComponent(): ZxSelectComponent {
    return new ZxSelectComponent({markForCheck: () => {}} as ChangeDetectorRef);
  }

  it('does not emit a change when options are assigned before the control value', () => {
    const component = createComponent();
    const onChange = jasmine.createSpy('onChange');

    component.options = options;
    component.registerOnChange(onChange);
    component.writeValue('1');

    expect(component.value).toBe('1');
    expect(onChange).not.toHaveBeenCalled();
  });

  it('preserves the control value when options arrive asynchronously', () => {
    const component = createComponent();
    const onChange = jasmine.createSpy('onChange');

    component.registerOnChange(onChange);
    component.writeValue('1');
    component.options = options;

    expect(component.value).toBe('1');
    expect(component.isSelected('1')).toBeTrue();
    expect(onChange).not.toHaveBeenCalled();
  });

  it('never adopts an option as the control value on its own', () => {
    const component = createComponent();
    const onChange = jasmine.createSpy('onChange');

    component.registerOnChange(onChange);
    component.writeValue('');
    component.options = options;
    component.setDisabledState(false);

    expect(component.value).toBe('');
    expect(onChange).not.toHaveBeenCalled();
  });

  it('shows a blank option while the value matches none', () => {
    const component = createComponent();
    component.options = options;
    component.writeValue('');

    expect(component.blankOption).toBeTrue();

    component.writeValue('1');

    expect(component.blankOption).toBeFalse();
  });

  it('leaves the empty state to the placeholder when there is one', () => {
    const component = createComponent();
    component.placeholder = '—';
    component.options = options;
    component.writeValue('');

    expect(component.blankOption).toBeFalse();
  });

  it('emits only a user selection change', () => {
    const component = createComponent();
    const onChange = jasmine.createSpy('onChange');
    component.registerOnChange(onChange);
    component.options = options;
    component.writeValue('0');

    component.onSelectionChange({target: {value: '1'}} as unknown as Event);

    expect(component.value).toBe('1');
    expect(onChange).toHaveBeenCalledOnceWith('1');
  });
});
