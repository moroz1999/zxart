import {Directive, HostBinding, Input} from '@angular/core';

type GridSpan = '1' | '2' | '3' | '4' | '5' | '6';

@Directive({
  selector: '[zxGridItem]',
  standalone: true,
})
export class ZxGridItemDirective {
  @Input() zxGridDesktopSpan: GridSpan = '1';
  @Input() zxGridMobileSpan: GridSpan | null = null;
  @Input() zxGridTabletSpan: GridSpan | null = null;

  @HostBinding('style.--zx-grid-item-desktop-span')
  get hostDesktopSpan(): GridSpan {
    return this.zxGridDesktopSpan;
  }

  @HostBinding('style.--zx-grid-item-mobile-span')
  get hostMobileSpan(): GridSpan {
    return this.zxGridMobileSpan ?? this.zxGridDesktopSpan;
  }

  @HostBinding('style.--zx-grid-item-tablet-span')
  get hostTabletSpan(): GridSpan {
    return this.zxGridTabletSpan ?? this.zxGridDesktopSpan;
  }
}
