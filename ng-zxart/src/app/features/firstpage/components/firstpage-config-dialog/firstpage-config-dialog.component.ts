import {Component, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule} from '@angular/forms';
import {MatDialogModule, MatDialogRef} from '@angular/material/dialog';
import {MatButtonModule} from '@angular/material/button';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {MatIconModule} from '@angular/material/icon';
import {CdkDragDrop, DragDropModule, moveItemInArray} from '@angular/cdk/drag-drop';
import {TranslateModule} from '@ngx-translate/core';
import {FirstpageConfigService} from '../../services/firstpage-config.service';
import {MODULE_MIN_RATING_PREF_CODES, ModuleConfig, ModuleType,} from '../../models/firstpage-config';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxInputComponent} from '../../../../shared/ui/zx-input/zx-input.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';

@Component({
  selector: 'app-firstpage-config-dialog',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    MatDialogModule,
    MatButtonModule,
    MatCheckboxModule,
    MatIconModule,
    DragDropModule,
    TranslateModule,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    ZxInputComponent,
    ZxHeading2Directive,
  ],
  templateUrl: './firstpage-config-dialog.component.html',
  styleUrls: ['./firstpage-config-dialog.component.scss']
})
export class FirstpageConfigDialogComponent implements OnInit {
  modules: ModuleConfig[] = [];

  constructor(
    private configService: FirstpageConfigService,
    private dialogRef: MatDialogRef<FirstpageConfigDialogComponent>,
  ) {}

  ngOnInit(): void {
    const config = this.configService.getCurrentConfig();
    this.modules = config.modules.map(m => ({...m, settings: {...m.settings}}));
  }

  hasMinRating(type: ModuleType): boolean {
    return type in MODULE_MIN_RATING_PREF_CODES;
  }

  onDrop(event: CdkDragDrop<ModuleConfig[]>): void {
    moveItemInArray(this.modules, event.previousIndex, event.currentIndex);
  }

  save(): void {
    this.configService.saveConfig(this.modules);
    this.dialogRef.close(true);
  }

  reset(): void {
    this.configService.resetToDefaults();
    this.ngOnInit();
  }

  close(): void {
    this.dialogRef.close(false);
  }
}
