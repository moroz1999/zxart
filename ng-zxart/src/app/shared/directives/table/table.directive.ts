import {Directive, HostBinding} from '@angular/core';

// Do not use this directive without wrapping the table in <zx-table>.
@Directive({
  selector: '[zxTable]',
  standalone: true
})
export class ZxTableDirective {
  @HostBinding('class.zx-table') className = true;
}
