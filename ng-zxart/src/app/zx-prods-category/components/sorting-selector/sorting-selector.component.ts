import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {SelectorDto} from '../../models/selector-dto';

@Component({
    selector: 'app-sorting-selector',
    templateUrl: './sorting-selector.component.html',
    styleUrls: ['./sorting-selector.component.scss'],
})
export class SortingSelectorComponent implements OnChanges {
    @Input() sortingSelector!: SelectorDto;
    @Output() sortingSelected = new EventEmitter<string>();
    sorting: string = '';

    constructor() {
    }

    ngOnChanges(): void {
        for (const group of this.sortingSelector) {
            for (const sorting of group.values) {
                if (sorting.selected) {
                    this.sorting = sorting.value;
                }
            }
        }
    }

    dataChanged() {
        this.sortingSelected.emit(this.sorting);
    }
}
