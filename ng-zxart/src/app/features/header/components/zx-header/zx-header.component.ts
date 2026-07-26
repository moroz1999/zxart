import {ChangeDetectionStrategy, Component, HostBinding, OnInit} from '@angular/core';
import {RouterLink} from '@angular/router';
import {MobileNavComponent} from '../mobile-nav/mobile-nav.component';
import {MenuBlockComponent} from '../../../menu/components/menu-block/menu-block.component';
import {SearchTriggerComponent} from '../search-trigger/search-trigger.component';
import {LanguageTriggerComponent} from '../language-trigger/language-trigger.component';
import {ThemeTriggerComponent} from '../theme-trigger/theme-trigger.component';
import {
  PictureSettingsTriggerComponent
} from '../../../picture-settings/components/picture-settings-trigger/picture-settings-trigger.component';
import {LoginTriggerComponent} from '../login-trigger/login-trigger.component';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-header, zx-header-view',
  standalone: true,
  imports: [
    MobileNavComponent,
    MenuBlockComponent,
    SearchTriggerComponent,
    LanguageTriggerComponent,
    ThemeTriggerComponent,
    PictureSettingsTriggerComponent,
    LoginTriggerComponent,
    SvgIconComponent,
    RouterLink,
  ],
  templateUrl: './zx-header.component.html',
  styleUrls: ['./zx-header.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHeaderComponent implements OnInit {
  @HostBinding('attr.role') readonly role = 'banner';

  constructor(
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}logo.svg`, 'logo')?.subscribe();
  }
}
