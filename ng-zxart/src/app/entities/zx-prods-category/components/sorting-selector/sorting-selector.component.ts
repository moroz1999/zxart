import {Component, EventEmitter, Input, OnChanges, OnDestroy, Output} from '@angular/core';
import {SelectorDto} from '../../models/selector-dto';
import {TranslateService} from '@ngx-translate/core';
import {FormsModule} from '@angular/forms';
import {ZxSelectComponent, ZxSelectOption} from '../../../../shared/ui/zx-select/zx-select.component';
import {map, Subject, Subscription, switchMap} from 'rxjs';

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
export class SortingSelectorComponent implements OnChanges, OnDestroy {
    @Input() sortingSelector!: SelectorDto;
    @Output() sortingSelected = new EventEmitter<string>();
    sorting = '';
    options: ZxSelectOption[] = [];

    private readonly inputChange$ = new Subject<SelectorDto>();
    private readonly subscriptions = new Subscription();

    constructor(private readonly translateService: TranslateService) {
        this.subscriptions.add(
            this.inputChange$.pipe(
                switchMap(selector => {
                    const keys = selector.flatMap(group =>
                        group.values.map(v => 'prods-list.sorting.' + v.title)
                    );
                    return this.translateService.stream(keys).pipe(
                        map(translations => ({selector, translations: translations as Record<string, string>}))
                    );
                })
            ).subscribe(({selector, translations}) => {
                this.sorting = '';
                this.options = [];
                for (const group of selector) {
                    for (const sortingValue of group.values) {
                        if (sortingValue.selected) {
                            this.sorting = sortingValue.value;
                        }
                        this.options.push({
                            value: sortingValue.value,
                            label: translations['prods-list.sorting.' + sortingValue.title] ?? sortingValue.value,
                        });
                    }
                }
            })
        );
    }

    ngOnChanges(): void {
        this.inputChange$.next(this.sortingSelector);
    }

    ngOnDestroy(): void {
        this.subscriptions.unsubscribe();
    }

    dataChanged(): void {
        this.sortingSelected.emit(this.sorting);
    }
}
