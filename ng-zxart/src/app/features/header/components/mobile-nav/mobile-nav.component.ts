import {ChangeDetectionStrategy, Component} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {MobileNavDrawerComponent} from '../mobile-nav-drawer/mobile-nav-drawer.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-mobile-nav',
  standalone: true,
  imports: [
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './mobile-nav.component.html',
  styleUrls: ['./mobile-nav.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class MobileNavComponent {
  constructor(
    private iconReg: SvgIconRegistryService,
    private dialog: Dialog,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}menu.svg`, 'menu')?.subscribe();
  }

  open(): void {
    this.dialog.open(MobileNavDrawerComponent, {
      panelClass: 'zx-mobile-nav-drawer',
      backdropClass: 'zx-mobile-nav-backdrop',
    });
  }
}
