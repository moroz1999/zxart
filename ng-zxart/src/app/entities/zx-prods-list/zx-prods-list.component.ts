import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {ZxProdsList} from './models/zx-prods-list';
import {ElementsService} from '../../shared/services/elements.service';
import {ZxProdsListDto} from './models/zx-prods-list-dto';
import {ZxProd} from '../../shared/models/zx-prod';
import {TranslatePipe} from '@ngx-translate/core';
import {ZxProdBlockComponent} from '../../shared/ui/zx-prod-block/zx-prod-block.component';
import {AsyncPipe, NgForOf, NgIf} from '@angular/common';
import {BehaviorSubject, Observable, of} from 'rxjs';
import {map} from 'rxjs/operators';
import {
  ZxProdsListSkeletonComponent
} from '../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';

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
    imports: [TranslatePipe, ZxProdBlockComponent, NgIf, NgForOf, AsyncPipe, ZxProdsListSkeletonComponent],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsListComponent implements OnInit {
    @Input() public property: 'prods' | 'publishedProds' | 'releases' | 'compilations' | 'seriesProds' = 'prods';
    @Input() elementId: number = 0;
    @Input() layout: 'years' | 'list' = 'list';
    @Input() skeletonCount = 4;
    @Input() imagesLayout: 'loading' | 'screenshots' | 'inlays' | 'table' = 'loading';
    @Input() set items$(value: Observable<ZxProd[] | null> | null) {
        this.hasItemsObservableInput = value !== null;
        if (value !== null) {
            this.vm$ = value.pipe(map(items => this.buildVm(items)));
        }
    }
    @Input() set items(value: ZxProd[] | null) {
        this.hasItemsInput = true;
        this.itemsStore.next(value);
        if (this.initialized) {
            this.useItemsInput();
        }
    }

    public vm$: Observable<ZxProdsListVm | null> = of(null);
    private readonly itemsStore = new BehaviorSubject<ZxProd[] | null>(null);
    private hasItemsObservableInput = false;
    private hasItemsInput = false;
    private initialized = false;

    constructor(
        private elementsService: ElementsService,
    ) {
    }

    ngOnInit(): void {
        this.initialized = true;
        if (this.hasItemsObservableInput) {
            return;
        }
        if (this.hasItemsInput) {
            this.useItemsInput();
            return;
        }
        this.fetchPrefetchedModel();
    }

    private useItemsInput(): void {
        this.vm$ = this.itemsStore.pipe(map(items => this.buildVm(items)));
    }

    private fetchPrefetchedModel(): void {
        this.vm$ = this.elementsService.getPrefetchedModel<ZxProdsListDto, ZxProdsList>(
            this.elementId,
            ZxProdsList,
        ).pipe(
            map(model => {
                const items = this.getItems(model);
                return this.buildVm(items);
            }),
        );
    }

    private buildVm(items: ZxProd[] | null): ZxProdsListVm {
        return {
            items,
            years: items === null ? null : this.getYears(items),
        };
    }

    private getItems(model: ZxProdsList): ZxProd[] {
        switch (this.property) {
            case 'compilations':
                return model.compilations;
            case 'seriesProds':
                return model.seriesProds;
            case 'publishedProds':
                return model.publishedProds;
            case 'releases':
                return model.releases;
            case 'prods':
            default:
                return model.prods;
        }
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
