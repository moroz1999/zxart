import {ChangeDetectionStrategy, Component, Inject} from '@angular/core';
import {DIALOG_DATA, DialogRef} from '@angular/cdk/dialog';
import {SelectorDto} from '../../../models/selector-dto';
import {TranslatePipe} from '@ngx-translate/core';
import {NgForOf, NgIf} from '@angular/common';
import {ZxCheckboxFieldComponent} from '../../../../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxButtonComponent} from '../../../../../shared/ui/zx-button/zx-button.component';
import {FormsModule} from '@angular/forms';

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
        ZxButtonComponent,
        TranslatePipe,
        ZxCheckboxFieldComponent,
        NgForOf,
        FormsModule,
        NgIf,
    ],
    standalone: true,
})
export class DialogSelectorDialogComponent {
    selectedValues: { [key: string]: boolean; } = {};
    amountInRow = 10;

    constructor(
        @Inject(DIALOG_DATA) public data: DialogData,
        private dialogRef: DialogRef<{ [key: string]: boolean }, DialogSelectorDialogComponent>,
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

    reset(): void {
        this.dialogRef.close({});
    }

    apply(): void {
        this.dialogRef.close(this.selectedValues);
    }
}
