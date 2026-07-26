import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxProd} from '../../shared/models/zx-prod';
import {TranslatePipe} from '@ngx-translate/core';
import {ZxProdBlockComponent} from '../zx-prod-block/zx-prod-block.component';
import {AsyncPipe, NgForOf, NgIf} from '@angular/common';
import {BehaviorSubject, Observable} from 'rxjs';
import {map, switchMap} from 'rxjs/operators';
import {
  ZxProdsListSkeletonComponent
} from '../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxProdsGridDirective} from '../../shared/directives/prods-grid.directive';

export interface YearProds {
    readonly year: number,
    readonly items: ZxProd[],
}

interface ZxProdsListVm {
    readonly items: ZxProd[] | null;
    readonly years: YearProds[] | null;
}

@Component({
    selector: 'zx-prods-list, zx-prods-list-view',
    templateUrl: './zx-prods-list.component.html',
    styleUrls: ['./zx-prods-list.component.scss'],
    imports: [TranslatePipe, ZxProdBlockComponent, NgIf, NgForOf, AsyncPipe, ZxProdsListSkeletonComponent, ZxProdsGridDirective],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsListComponent {
    @Input() layout: 'years' | 'list' = 'list';
    @Input() skeletonCount = 4;
    @Input() imagesLayout: 'loading' | 'screenshots' | 'inlays' | 'table' = 'loading';
    /** Items as a stream, for callers that load them asynchronously. */
    @Input() set items$(value: Observable<ZxProd[] | null> | null) {
        this.itemsSource.next(value ?? this.itemsStore);
    }

    @Input() set items(value: ZxProd[] | null) {
        this.itemsStore.next(value);
        this.itemsSource.next(this.itemsStore);
    }

    private readonly itemsStore = new BehaviorSubject<ZxProd[] | null>(null);
    private readonly itemsSource = new BehaviorSubject<Observable<ZxProd[] | null>>(this.itemsStore);

    public vm$: Observable<ZxProdsListVm | null> = this.itemsSource.pipe(
        switchMap(items$ => items$),
        map(items => this.buildVm(items)),
    );

    private buildVm(items: ZxProd[] | null): ZxProdsListVm {
        return {
            items,
            years: items === null ? null : this.getYears(items),
        };
    }


    private getYears(items: ZxProd[]): YearProds[] {
        let years = [] as Array<YearProds>;
        items.map(zxProd => {
            let prodYear = years.find(year => year.year === +zxProd.year);
            if (!prodYear) {
                prodYear = {
                    year: +zxProd.year,
                    items: [],
                };
                years.push(prodYear);
            }
            prodYear.items.push(zxProd);
        });
        years.sort((a, b) => a.year - b.year);
        return years;
    }
}
