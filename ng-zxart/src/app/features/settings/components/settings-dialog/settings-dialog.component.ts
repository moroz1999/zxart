import {ChangeDetectionStrategy, Component, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {DialogRef} from '@angular/cdk/dialog';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {ThemeService} from '../../services/theme.service';
import {Theme} from '../../models/preference.dto';
import {ZxToggleComponent, ZxToggleOption} from '../../../../shared/ui/zx-toggle/zx-toggle.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxDialogComponent} from '../../../../shared/ui/zx-dialog/zx-dialog.component';
import {ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'zx-settings-dialog',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxDialogComponent,
    ZxToggleComponent,
    ZxStackComponent,
    ZxHeading3Directive,
  ],
  templateUrl: './settings-dialog.component.html',
  styleUrls: ['./settings-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SettingsDialogComponent implements OnDestroy {
  themeOptions: ZxToggleOption[] = [];

  private readonly subscriptions = new Subscription();

  constructor(
    private dialogRef: DialogRef<SettingsDialogComponent>,
    private themeService: ThemeService,
    private translateService: TranslateService,
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
