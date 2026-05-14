import {ChangeDetectionStrategy, Component, HostBinding, OnInit} from '@angular/core';
import {toSignal} from '@angular/core/rxjs-interop';
import {map} from 'rxjs/operators';
import {BackendLinksService} from '../../services/backend-links.service';
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
  selector: 'zx-header',
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
  ],
  templateUrl: './zx-header.component.html',
  styleUrls: ['./zx-header.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxHeaderComponent implements OnInit {
  @HostBinding('attr.role') readonly role = 'banner';

  readonly homeUrl = toSignal(
    this.backendLinksService.links$.pipe(map(l => l.homeUrl)),
    {initialValue: null},
  );

  constructor(
    private backendLinksService: BackendLinksService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}logo.svg`, 'logo')?.subscribe();
  }
}
