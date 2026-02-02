import {Component, Inject, OnInit} from '@angular/core';
import {
  MAT_DIALOG_DATA,
  MatDialogActions,
  MatDialogContent,
  MatDialogRef,
  MatDialogTitle,
} from '@angular/material/dialog';
import {SelectorDto} from '../../../models/selector-dto';
import {TranslatePipe} from '@ngx-translate/core';
import {NgForOf, NgIf} from '@angular/common';
import {ZxCheckboxFieldComponent} from '../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {FormsModule} from '@angular/forms';

interface DialogData {
    selectValuesLabel: string;
    selectorData: SelectorDto;
}

@Component({
    selector: 'app-dialog-selector-dialog',
    templateUrl: './dialog-selector-dialog.component.html',
    styleUrls: ['./dialog-selector-dialog.component.scss'],
    imports: [
        ZxButtonComponent,
        TranslatePipe,
        ZxCheckboxFieldComponent,
        MatDialogActions,
        MatDialogTitle,
        MatDialogContent,
        NgForOf,
        FormsModule,
        NgIf,
    ],
    standalone: true,
})
export class DialogSelectorDialogComponent implements OnInit {
    selectedValues: { [key: string]: boolean; } = {};
    amountInRow = 10;

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: DialogData,
        private dialogRef: MatDialogRef<DialogSelectorDialogComponent>,
    ) {
        for (const group of data.selectorData) {
            for (const value of group.values) {
                if (value.selected) {
                    this.selectedValues[value.value] = true;
                }
            }
        }
    }

    ngOnInit(): void {
    }

    getColumns(length: number): number {
        return Math.ceil(length / this.amountInRow);
    }

    reset(): void {
        this.dialogRef.close({});
    }

    apply(): void {
        this.dialogRef.close(this.selectedValues);
    }
}
