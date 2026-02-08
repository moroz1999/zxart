import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {SelectorDto} from '../../models/selector-dto';
import {TranslateService} from '@ngx-translate/core';
import {FormsModule} from '@angular/forms';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';

@Component({
    selector: 'zx-sorting-selector',
    templateUrl: './sorting-selector.component.html',
    styleUrls: ['./sorting-selector.component.scss'],
    standalone: true,
    imports: [
        FormsModule,
        ZxSelectComponent,
    ],
})
export class SortingSelectorComponent implements OnChanges {
    @Input() sortingSelector!: SelectorDto;
    @Output() sortingSelected = new EventEmitter<string>();
    sorting = '';
    options: ZxSelectOption[] = [];

    constructor(private translateService: TranslateService) {
    }

    ngOnChanges(): void {
        this.options = [];
        for (const group of this.sortingSelector) {
            for (const sortingValue of group.values) {
                if (sortingValue.selected) {
                    this.sorting = sortingValue.value;
                }
                this.options.push({
                    value: sortingValue.value,
                    label: this.translateService.instant('prods-list.sorting.' + sortingValue.title),
                });
            }
        }
    }

    dataChanged(): void {
        this.sortingSelected.emit(this.sorting);
    }
}
