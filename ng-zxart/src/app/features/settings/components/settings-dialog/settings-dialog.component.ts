import {Component} from '@angular/core';
import {CommonModule} from '@angular/common';
import {MatDialogModule, MatDialogRef} from '@angular/material/dialog';
import {MatIconModule} from '@angular/material/icon';
import {MatButtonModule} from '@angular/material/button';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {ThemeService} from '../../services/theme.service';
import {Theme} from '../../models/preference.dto';
import {ZxToggleComponent, ZxToggleOption} from '../../../../shared/ui/zx-toggle/zx-toggle.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxHeading3Directive} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'app-settings-dialog',
  standalone: true,
  imports: [
    CommonModule,
    MatDialogModule,
    MatIconModule,
    MatButtonModule,
    TranslateModule,
    ZxToggleComponent,
    ZxStackComponent,
    ZxHeading3Directive
  ],
  templateUrl: './settings-dialog.component.html',
  styleUrls: ['./settings-dialog.component.scss']
})
export class SettingsDialogComponent {
  themeOptions: ZxToggleOption[] = [];

  constructor(
    private dialogRef: MatDialogRef<SettingsDialogComponent>,
    private themeService: ThemeService,
    private translateService: TranslateService
  ) {
    this.initThemeOptions();
  }

  get currentTheme(): Theme {
    return this.themeService.currentTheme;
  }

  private initThemeOptions(): void {
    this.themeOptions = [
      {value: 'light', label: this.translateService.instant('settings.theme.light')},
      {value: 'dark', label: this.translateService.instant('settings.theme.dark')}
    ];
  }

  onThemeChange(theme: string): void {
    this.themeService.setTheme(theme as Theme);
  }

  close(): void {
    this.dialogRef.close();
  }
}
