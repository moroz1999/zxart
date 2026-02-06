import {Directive, HostBinding} from '@angular/core';

@Directive({
  selector: '[zxTable]',
  standalone: true
})
export class ZxTableDirective {
  @HostBinding('class.zx-table') className = true;
}
