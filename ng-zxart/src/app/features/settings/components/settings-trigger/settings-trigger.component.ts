import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {Dialog} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {SettingsDialogComponent} from '../settings-dialog/settings-dialog.component';
import {ThemeService} from '../../services/theme.service';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-settings-trigger',
  standalone: true,
  imports: [
    SvgIconComponent,
    ZxButtonComponent,
  ],
  templateUrl: './settings-trigger.component.html',
  styleUrls: ['./settings-trigger.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SettingsTriggerComponent implements OnInit {
  constructor(
    private dialog: Dialog,
    private themeService: ThemeService,
    private iconReg: SvgIconRegistryService,
  ) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}settings.svg`, 'settings')?.subscribe();
    this.themeService.initialize();
  }

  openSettings(): void {
    this.dialog.open(SettingsDialogComponent, {
      panelClass: 'zx-dialog',
      backdropClass: 'zx-dialog-backdrop',
    });
  }
}
