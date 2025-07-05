import {Component, Inject, OnInit} from '@angular/core';
import {MAT_DIALOG_DATA} from '@angular/material/dialog';
import {SelectorDto} from '../../../models/selector-dto';

interface DialogData {
    selectValuesLabel: string;
    selectorData: SelectorDto;
}

@Component({
    selector: 'app-dialog-selector-dialog',
    templateUrl: './dialog-selector-dialog.component.html',
    styleUrls: ['./dialog-selector-dialog.component.scss'],
})
export class DialogSelectorDialogComponent implements OnInit {
    selectedValues: { [key: string]: boolean; } = {};
    amountInRow = 10;

    constructor(
        @Inject(MAT_DIALOG_DATA) public data: DialogData,
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
}
