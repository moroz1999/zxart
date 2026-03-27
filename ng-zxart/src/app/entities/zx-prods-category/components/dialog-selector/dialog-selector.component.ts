import {ChangeDetectionStrategy, Component, EventEmitter, Input, OnChanges, OnInit, Output} from '@angular/core';
import {Dialog} from '@angular/cdk/dialog';
import {DialogSelectorDialogComponent} from './dialog-selector-dialog/dialog-selector-dialog.component';
import {SelectorDto} from '../../models/selector-dto';
import {NgIf} from '@angular/common';
import {ZxButtonComponent} from "../../../../shared/ui/zx-button/zx-button.component";

@Component({
    selector: 'zx-dialog-selector',
    templateUrl: './dialog-selector.component.html',
    styleUrls: ['./dialog-selector.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        ZxButtonComponent,
        NgIf,
    ],
})
export class DialogSelectorComponent implements OnInit, OnChanges {
    @Input() selectorData!: SelectorDto;
    @Input() selectedValuesLabel!: string;
    @Input() selectValuesLabel!: string;
    @Input() width = '30rem';
    selectedValues: Array<string> = [];
    @Output() newValues = new EventEmitter<Array<string>>();
    public value = 0;

    constructor(
        public dialog: Dialog,
    ) {
    }

    ngOnInit(): void {

    }

    ngOnChanges() {
        this.selectedValues = [];
        if (this.selectorData) {
            for (const group of this.selectorData) {
                if (group && group.values) {
                    for (const value of group.values) {
                        if (value.selected) {
                            this.selectedValues.push(value.title);
                        }
                    }
                }
            }
        }
    }

    clickHandler() {
        let dialogRef = this.dialog.open(DialogSelectorDialogComponent, {
            width: this.width,
            panelClass: 'zx-dialog',
            backdropClass: 'zx-dialog-backdrop',
            data: {
                selectorData: this.selectorData,
                selectValuesLabel: this.selectValuesLabel,
            },
        });
        dialogRef.closed.subscribe((result) => {
            const typedResult = result as { [key: string]: boolean } | undefined;
            if (typedResult !== undefined) {
                let values = [] as Array<string>;
                if (typedResult) {
                    for (let value in typedResult) {
                        if (typedResult.hasOwnProperty(value)) {
                            if (typedResult[value]) {
                                values.push(value);
                            }
                        }
                    }
                }
                this.newValues.emit(values);
            }
        });
    }

    getSelectedValuesString() {
        return this.selectedValues.join(', ');
    }
}
