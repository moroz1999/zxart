import {CommonModule} from '@angular/common';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {NavigationEnd, Router} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {combineLatest, Observable} from 'rxjs';
import {filter, map, startWith} from 'rxjs/operators';
import {RootPrivilegeService} from '../../../../shared/services/root-privilege.service';
import {MANAGE_SECTIONS, ManageSection} from '../../manage-sections';
import {ZxTabComponent} from '../../../../shared/ui/zx-tabs/zx-tab.component';
import {ZxTabsComponent} from '../../../../shared/ui/zx-tabs/zx-tabs.component';

interface ManageTabsVm {
  sections: ManageSection[];
  activeIndex: number;
}

/**
 * Navigation between the management sections.
 *
 * Each section is its own route, so the tabs are routed links and the active
 * one follows the URL. A section the user holds no privilege on is left out
 * entirely — the route guard would send them home anyway.
 */
@Component({
  selector: 'zx-manage-tabs',
  standalone: true,
  imports: [CommonModule, TranslateModule, ZxTabComponent, ZxTabsComponent],
  templateUrl: './zx-manage-tabs.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxManageTabsComponent {
  readonly vm$: Observable<ManageTabsVm> = combineLatest([
    combineLatest(MANAGE_SECTIONS.map(section => this.rootPrivilegeService.has(section.privilege))),
    this.router.events.pipe(
      filter(event => event instanceof NavigationEnd),
      startWith(null),
      map(() => this.router.url),
    ),
  ]).pipe(
    map(([allowed, url]) => {
      const sections = MANAGE_SECTIONS.filter((_, index) => allowed[index]);
      // an edit route lives under its section's path, so it keeps its tab active
      const activeIndex = sections.findIndex(section => url.startsWith(section.href));
      return {sections, activeIndex: Math.max(activeIndex, 0)};
    }),
  );

  constructor(
    private readonly rootPrivilegeService: RootPrivilegeService,
    private readonly router: Router,
  ) {}
}
