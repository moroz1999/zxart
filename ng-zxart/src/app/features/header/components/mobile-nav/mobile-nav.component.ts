import {ChangeDetectionStrategy, Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {animate, style, transition, trigger} from '@angular/animations';
import {Observable} from 'rxjs';
import {shareReplay, switchMap} from 'rxjs/operators';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPopoverMenuItemComponent} from '../../../../shared/ui/zx-popover-menu-item/zx-popover-menu-item.component';
import {MenuService} from '../../../menu/services/menu.service';
import {CurrentRouteService} from '../../services/current-route.service';
import {CurrentLanguageService} from '../../services/current-language.service';
import {MenuItem} from '../../../menu/models/menu-item';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-mobile-nav',
  standalone: true,
  imports: [CommonModule, TranslateModule, SvgIconComponent, ZxButtonComponent, ZxPopoverMenuItemComponent],
  templateUrl: './mobile-nav.component.html',
  styleUrls: ['./mobile-nav.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('drawer', [
      transition(':enter', [
        style({transform: 'translateX(-100%)'}),
        animate('280ms cubic-bezier(0.22, 1, 0.36, 1)', style({transform: 'translateX(0)'})),
      ]),
      transition(':leave', [
        animate('200ms cubic-bezier(0.64, 0, 0.78, 0)', style({transform: 'translateX(-100%)'})),
      ]),
    ]),
    trigger('backdrop', [
      transition(':enter', [
        style({opacity: 0}),
        animate('200ms ease', style({opacity: 1})),
      ]),
      transition(':leave', [
        animate('180ms ease', style({opacity: 0})),
      ]),
    ]),
  ],
})
export class MobileNavComponent {
  readonly items$: Observable<MenuItem[]> = this.languageService.languageCode$.pipe(
    switchMap(code => this.menuService.getMenuItems(code)),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  isOpen = false;

  constructor(
    private menuService: MenuService,
    private languageService: CurrentLanguageService,
    private routeService: CurrentRouteService,
    private iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}menu.svg`, 'menu')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}x.svg`, 'mn-x')?.subscribe();
  }

  open(): void {
    this.isOpen = true;
  }

  close(): void {
    this.isOpen = false;
  }

  isActive(item: MenuItem): boolean {
    return this.routeService.isActive(item.url);
  }
}
