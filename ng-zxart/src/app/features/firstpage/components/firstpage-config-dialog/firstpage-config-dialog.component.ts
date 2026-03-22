import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {DialogRef} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CdkDragDrop, DragDropModule, moveItemInArray} from '@angular/cdk/drag-drop';
import {TranslateModule} from '@ngx-translate/core';
import {Subscription} from 'rxjs';
import {FirstpageConfigService} from '../../services/firstpage-config.service';
import {UserPreferencesService} from '../../../settings/services/user-preferences.service';
import {
  MODULE_MIN_RATING_PREF_CODES,
  MODULE_START_YEAR_PREF_CODES,
  ModuleConfig,
  ModuleType,
} from '../../models/firstpage-config';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {ZxCheckboxFieldComponent} from '../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {environment} from '../../../../../environments/environment';

@Component({
  selector: 'zx-firstpage-config-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SvgIconComponent,
    DragDropModule,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInputComponent,
    ZxSelectComponent,
    ZxCheckboxFieldComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './firstpage-config-dialog.component.html',
  styleUrls: ['./firstpage-config-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FirstpageConfigDialogComponent implements OnInit, OnDestroy {
  modules: ModuleConfig[] = [];
  readonly startYearOptions: ZxSelectOption[];
  private configSub?: Subscription;

  constructor(
    private configService: FirstpageConfigService,
    private preferencesService: UserPreferencesService,
    private dialogRef: DialogRef<boolean, FirstpageConfigDialogComponent>,
    private iconReg: SvgIconRegistryService,
  ) {
    const currentYear = new Date().getFullYear();
    this.startYearOptions = Array.from({length: 11}, (_, i) => ({
      value: String(i),
      label: String(currentYear - i),
    }));
  }

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}drag-indicator.svg`, 'drag-indicator')?.subscribe();
    this.configSub = this.configService.getCurrentConfig().subscribe(config => {
      this.modules = config.modules.map(m => ({...m, settings: {...m.settings}}));
    });
  }

  ngOnDestroy(): void {
    this.configSub?.unsubscribe();
  }

  hasMinRating(type: ModuleType): boolean {
    return type in MODULE_MIN_RATING_PREF_CODES;
  }

  hasStartYear(type: ModuleType): boolean {
    return type in MODULE_START_YEAR_PREF_CODES;
  }

  getStartYearStr(mod: ModuleConfig): string {
    return String(mod.settings.startYearOffset ?? 0);
  }

  setStartYearStr(mod: ModuleConfig, value: string): void {
    mod.settings.startYearOffset = parseInt(value, 10) || 0;
  }

  onDrop(event: CdkDragDrop<ModuleConfig[]>): void {
    moveItemInArray(this.modules, event.previousIndex, event.currentIndex);
  }

  save(): void {
    this.configService.saveConfig(this.modules);
    this.dialogRef.close(true);
  }

  reset(): void {
    this.preferencesService.getDefaults().subscribe(defaults => {
      this.modules = this.configService.buildDefaultModules(defaults);
      this.configService.resetToDefaults().subscribe();
    });
  }

  close(): void {
    this.dialogRef.close(false);
  }
}
