import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DialogRef} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ThemeService} from '../../services/theme.service';
import {Theme} from '../../models/preference.dto';
import {ZxToggleComponent, ZxToggleOption} from '../../../../shared/ui/zx-toggle/zx-toggle.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-settings-dialog',
  standalone: true,
  imports: [
    CommonModule,
    SvgIconComponent,
    TranslateModule,
    ZxToggleComponent,
    ZxStackComponent,
    ZxButtonComponent,
    ZxHeading3Directive,
  ],
  templateUrl: './settings-dialog.component.html',
  styleUrls: ['./settings-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SettingsDialogComponent implements OnInit, OnDestroy {
  themeOptions: ZxToggleOption[] = [];

  private readonly subscriptions = new Subscription();

  constructor(
    private dialogRef: DialogRef<SettingsDialogComponent>,
    private themeService: ThemeService,
    private translateService: TranslateService,
    private iconReg: SvgIconRegistryService,
  ) {
    this.subscriptions.add(
      this.translateService.stream(['settings.theme.light', 'settings.theme.dark'])
        .subscribe(translations => {
          this.themeOptions = [
            {value: 'light', label: translations['settings.theme.light']},
            {value: 'dark', label: translations['settings.theme.dark']},
          ];
        })
    );
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}close.svg`, 'close')?.subscribe();
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  get currentTheme(): Theme {
    return this.themeService.currentTheme;
  }

  onThemeChange(theme: string): void {
    this.themeService.setTheme(theme as Theme);
  }

  close(): void {
    this.dialogRef.close();
  }
}
