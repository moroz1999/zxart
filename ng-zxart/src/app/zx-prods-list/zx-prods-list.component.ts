import {Component, Input, OnInit} from '@angular/core';
import {ZxProdsList} from './models/zx-prods-list';
import {ElementsService} from '../shared/services/elements.service';
import {ZxProdsListDto} from './models/zx-prods-list-dto';
import {ZxProd} from '../shared/models/zx-prod';
import {TranslatePipe} from '@ngx-translate/core';
import {ZxProdBlockComponent} from '../zx-prod-block/zx-prod-block.component';
import {NgForOf, NgIf} from '@angular/common';

export interface YearProds {
    readonly year: number,
    readonly items: ZxProd[],
}

@Component({
    selector: 'app-zx-prods-list',
    templateUrl: './zx-prods-list.component.html',
    styleUrls: ['./zx-prods-list.component.scss'],
    imports: [TranslatePipe, ZxProdBlockComponent, NgIf, NgForOf, NgForOf, NgIf, NgIf, NgForOf],
    standalone: true,
})
export class ZxProdsListComponent implements OnInit {
    public model?: ZxProdsList;
    @Input() public property: 'prods' | 'publishedProds' | 'releases' | 'compilations' | 'seriesProds' = 'prods';
    @Input() elementId: number = 0;
    @Input() layout: 'years' | 'list' = 'list';
    private yearsList?: YearProds[];

    constructor(
        private elementsService: ElementsService,
    ) {
    }

    ngOnInit(): void {
        this.fetchPrefetchedModel();
    }

    private fetchPrefetchedModel(): void {
        this.elementsService.getPrefetchedModel<ZxProdsListDto, ZxProdsList>(this.elementId, ZxProdsList).subscribe(
            model => {
                this.model = model;
                this.yearsList = undefined;
            },
        );
    }

    public get items(): Array<ZxProd> | undefined {
        switch (this.property) {
            case 'compilations':
                return this.model?.compilations;
            case 'seriesProds':
                return this.model?.seriesProds;
            case 'publishedProds':
                return this.model?.publishedProds;
            case 'releases':
                return this.model?.releases;
            case 'prods':
            default:
                return this.model?.prods;
        }
    }

    public get years(): Array<YearProds> {
        if (this.yearsList) return this.yearsList;
        let years = [] as Array<YearProds>;
        this.items?.map(zxProd => {
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
        this.yearsList = years;
        return years;
    }
}
