import {ChangeDetectionStrategy, Component, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {DialogRef} from '@angular/cdk/dialog';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {CdkDragDrop, DragDropModule, moveItemInArray} from '@angular/cdk/drag-drop';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, map, of, Subscription, switchMap} from 'rxjs';
import {FirstpageConfigService} from '../../services/firstpage-config.service';
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
import {ZxDialogComponent} from '../../../../shared/ui/zx-dialog/zx-dialog.component';
import {LabelDirective} from '../../../../shared/ui/typography/directives/label.directive';
import {environment} from '../../../../../environments/environment';
import {ZxFormMessageComponent} from '../../../../shared/ui/zx-form/zx-form-message/zx-form-message.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

interface DialogState {
  saving: boolean;
  saveFailed: boolean;
}

@Component({
  selector: 'zx-firstpage-config-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    SvgIconComponent,
    DragDropModule,
    TranslateModule,
    ZxDialogComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInputComponent,
    ZxSelectComponent,
    ZxCheckboxFieldComponent,
    LabelDirective,
    ZxFormMessageComponent,
    ZxStackComponent,
  ],
  templateUrl: './firstpage-config-dialog.component.html',
  styleUrls: ['./firstpage-config-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class FirstpageConfigDialogComponent implements OnInit, OnDestroy {
  private readonly modulesStore = new BehaviorSubject<ModuleConfig[]>([]);
  private readonly stateStore = new BehaviorSubject<DialogState>({saving: false, saveFailed: false});
  private readonly subscriptions = new Subscription();

  readonly modules$ = this.modulesStore.asObservable();
  readonly state$ = this.stateStore.asObservable();
  readonly startYearOptions: ZxSelectOption[];

  constructor(
    private configService: FirstpageConfigService,
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
    const iconLoad = this.iconReg.loadSvg(`${environment.svgUrl}drag-indicator.svg`, 'drag-indicator');
    if (iconLoad) {
      this.subscriptions.add(iconLoad.subscribe());
    }

    this.subscriptions.add(
      this.configService.getCurrentConfig().subscribe(config => {
        this.modulesStore.next(this.cloneModules(config.modules));
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
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
    const modules = [...this.modulesStore.getValue()];
    moveItemInArray(modules, event.previousIndex, event.currentIndex);
    this.modulesStore.next(modules);
  }

  save(): void {
    if (this.stateStore.getValue().saving) {
      return;
    }

    this.stateStore.next({saving: true, saveFailed: false});
    this.subscriptions.add(
      this.configService.saveConfig(this.modulesStore.getValue()).subscribe(saved => {
        if (saved) {
          this.dialogRef.close(true);
          return;
        }
        this.stateStore.next({saving: false, saveFailed: true});
      }),
    );
  }

  reset(): void {
    if (this.stateStore.getValue().saving) {
      return;
    }

    this.stateStore.next({saving: true, saveFailed: false});
    this.subscriptions.add(
      this.configService.resetToDefaults().pipe(
        switchMap(saved => saved
          ? this.configService.getCurrentConfig().pipe(map(config => config.modules))
          : of(null)),
      ).subscribe(modules => {
        if (modules === null) {
          this.stateStore.next({saving: false, saveFailed: true});
          return;
        }

        this.modulesStore.next(this.cloneModules(modules));
        this.stateStore.next({saving: false, saveFailed: false});
      }),
    );
  }

  close(): void {
    if (!this.stateStore.getValue().saving) {
      this.dialogRef.close(false);
    }
  }

  private cloneModules(modules: ModuleConfig[]): ModuleConfig[] {
    return modules.map(module => ({...module, settings: {...module.settings}}));
  }
}
