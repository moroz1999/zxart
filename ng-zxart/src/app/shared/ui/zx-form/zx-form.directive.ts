import {Directive, HostBinding, Input} from '@angular/core';

export type ZxFormFieldsLayout = 'horizontal' | 'vertical';

/**
 * Form layout host directive. Apply to a native <form> element:
 * <form zxForm fieldsLayout="horizontal" mobileFieldsLayout="vertical" [sectionWrap]="true">
 */
@Directive({
  selector: '[zxForm]',
  standalone: true,
})
export class ZxFormDirective {
  @Input() fieldsLayout: ZxFormFieldsLayout = 'vertical';
  @Input() mobileFieldsLayout: ZxFormFieldsLayout = 'vertical';
  @Input() sectionWrap = false;
  /** Bordered appearance: fieldsets/sections separated by divider lines, action row as a footer strip. */
  @Input() divided = false;
  /** Number of columns the section field grids default to (1 or 2). */
  @Input() columns: 1 | 2 = 1;

  @HostBinding('class') get hostClass(): string {
    const classes = ['zx-form'];
    if (this.fieldsLayout === 'horizontal') {
      classes.push('zx-form--fields-horizontal');
    }
    if (this.mobileFieldsLayout === 'vertical') {
      classes.push('zx-form--mobile-vertical');
    }
    if (this.sectionWrap) {
      classes.push('zx-form--section-wrap');
    }
    if (this.divided) {
      classes.push('zx-form--divided');
    }
    if (this.columns === 2) {
      classes.push('zx-form--cols-2');
    }
    return classes.join(' ');
  }
}
