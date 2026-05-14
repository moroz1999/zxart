import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {SelectorDto} from '../../../models/selector-dto';
import {TranslatePipe} from '@ngx-translate/core';
import {NgForOf, NgIf} from '@angular/common';
import {ZxCheckboxFieldComponent} from '../../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxButtonComponent} from '../../../../../shared/ui/zx-button/zx-button.component';
import {ZxButtonControlsComponent} from '../../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxDialogComponent} from '../../../../../shared/ui/zx-dialog/zx-dialog.component';
import {FormsModule} from '@angular/forms';
import {TextDirective} from '../../../../../shared/ui/typography/directives/text.directive';

interface DialogData {
  selectValuesLabel: string;
  selectorData: SelectorDto;
}

@Component({
  selector: 'zx-dialog-selector-dialog',
  templateUrl: './dialog-selector-dialog.component.html',
  styleUrls: ['./dialog-selector-dialog.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    ZxDialogComponent,
    ZxButtonComponent,
    ZxButtonControlsComponent,
    TranslatePipe,
    ZxCheckboxFieldComponent,
    NgForOf,
    NgIf,
    FormsModule,
    TextDirective,
  ],
  standalone: true,
})
export class DialogSelectorDialogComponent {
  selectedValues: {[key: string]: boolean} = {};
  amountInRow = 10;

  constructor(
    @Inject(DIALOG_DATA) public data: DialogData,
    private dialogRef: DialogRef<{[key: string]: boolean} | undefined, DialogSelectorDialogComponent>,
  ) {
    for (const group of data.selectorData) {
      for (const value of group.values) {
        if (value.selected) {
          this.selectedValues[value.value] = true;
        }
      }
    }
  }

  getColumns(length: number): number {
    return Math.ceil(length / this.amountInRow);
  }

  close(): void {
    this.dialogRef.close(undefined);
  }

  reset(): void {
    this.dialogRef.close({});
  }

  apply(): void {
    this.dialogRef.close(this.selectedValues);
  }
}
