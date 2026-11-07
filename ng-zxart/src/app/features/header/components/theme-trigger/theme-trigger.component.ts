import {AsyncPipe} from '@angular/common';
import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {Observable} from 'rxjs';
import {ThemeService} from '../../../settings/services/theme.service';
import {ThemePreference} from '../../../settings/models/preference.dto';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-theme-trigger',
  standalone: true,
  imports: [
    TranslateModule,
    AsyncPipe,
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './theme-trigger.component.html',
  styleUrls: ['./theme-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ThemeTriggerComponent implements OnInit {
  readonly themePreference$: Observable<ThemePreference>;

  constructor(
    private themeService: ThemeService,
    private iconReg: SvgIconRegistryService,
  ) {
    this.themePreference$ = this.themeService.preference$;
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}theme.svg`, 'theme')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}theme-auto.svg`, 'theme-auto')?.subscribe();
    this.themeService.initialize();
  }

  toggle(): void {
    const currentTheme = this.themeService.currentTheme;
    const nextTheme = currentTheme === null ? 'dark' : currentTheme === 'dark' ? 'light' : null;
    this.themeService.setTheme(nextTheme);
  }
}
