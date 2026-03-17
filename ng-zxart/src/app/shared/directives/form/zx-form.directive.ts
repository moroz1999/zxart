import {Directive, HostBinding} from '@angular/core';

@Directive({
  selector: '[zxForm]',
  standalone: true,
})
export class ZxFormDirective {
  @HostBinding('class.zx-form') className = true;
}
