import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {ThemeService} from '../../../settings/services/theme.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-theme-trigger',
  standalone: true,
  imports: [
    TranslateModule,
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './theme-trigger.component.html',
  styleUrls: ['./theme-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ThemeTriggerComponent implements OnInit {
  constructor(
    private themeService: ThemeService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}theme.svg`, 'theme')?.subscribe();
    this.themeService.initialize();
  }

  toggle(): void {
    this.themeService.setTheme(this.themeService.currentTheme === 'dark' ? 'light' : 'dark');
  }
}
