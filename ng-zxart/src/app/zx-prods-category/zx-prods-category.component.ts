import {Component, ElementRef, HostBinding, Input, OnInit, ViewChild} from '@angular/core';
import {ElementsService, PostParameters} from '../shared/services/elements.service';
import {ZxProdCategory} from './models/zx-prod-category';
import {Tag} from '../shared/models/tag';
import {ZxProdCategoryDto} from './models/zx-prod-category-dto';
import {environment} from '../../environments/environment';
import {TranslatePipe} from '@ngx-translate/core';
import {ZxPaginationComponent} from '../shared/ui/zx-pagination/zx-pagination.component';
import {
  CategoriesTreeSelectorComponent,
} from './components/categories-tree-selector/categories-tree-selector.component';
import {SortingSelectorComponent} from './components/sorting-selector/sorting-selector.component';
import {DialogSelectorComponent} from './components/dialog-selector/dialog-selector.component';
import {LetterSelectorComponent} from './components/letter-selector/letter-selector.component';
import {MatProgressSpinner, MatSpinner} from '@angular/material/progress-spinner';
import {ZxProdBlockComponent} from '../zx-prod-block/zx-prod-block.component';
import {ZxProdRowComponent} from '../zx-prod-row/zx-prod-row.component';
import {FormsModule} from '@angular/forms';
import {CommonModule} from '@angular/common';
import {TagsSelectorComponent} from '../shared/components/tags-selector/tags-selector.component';
import {ZxCheckboxFieldComponent} from '../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxButtonComponent} from '../shared/ui/zx-button/zx-button.component';
import {ZxToggleComponent, ZxToggleOption} from '../shared/ui/zx-toggle/zx-toggle.component';

const defaultStatuses: string[] = ['allowed', 'forbidden', 'forbiddenzxart', 'allowedzxart', 'insales', 'donationware', 'recovered', 'unknown'];

export type ZxProdsListLayout = 'loading' | 'screenshots' | 'inlays' | 'table';

@Component({
    selector: 'app-zx-prods-category',
    templateUrl: './zx-prods-category.component.html',
    styleUrls: ['./zx-prods-category.component.scss'],
    imports: [
        TranslatePipe,
        ZxPaginationComponent,
        CategoriesTreeSelectorComponent,
        SortingSelectorComponent,
        DialogSelectorComponent,
        LetterSelectorComponent,
        MatSpinner,
        ZxProdBlockComponent,
        ZxProdRowComponent,
        FormsModule,
        CommonModule,
        TagsSelectorComponent,
        MatProgressSpinner,
        ZxButtonComponent,
        ZxToggleComponent,
        ZxCheckboxFieldComponent,
    ],
    standalone: true,
})
export class ZxProdsCategoryComponent implements OnInit {
    public model!: ZxProdCategory;
    public pagesAmount = 0;
    public currentPage = 1;
    public elementsOnPage = 100;

    public years: Array<string> = [];
    public hw: Array<string> = [];
    public languages: Array<string> = [];
    public legalStatuses: Array<string> = [];
    public formats: Array<string> = [];
    public releaseTypes: Array<string> = [];
    public letter?: string;
    public sorting?: string;
    public tags: Array<number> = [];
    public countries: Array<string> = [];
    public releases = false;
    public includeSubcategoriesProds = true;

    public layout: ZxProdsListLayout = 'loading';
    public loading = false;
    public urlBase = '';

    public layoutOptions: ZxToggleOption[] = [
        {value: 'loading', icon: 'photo'},
        {value: 'screenshots', icon: 'videogame_asset'},
        {value: 'inlays', icon: 'photo_camera'},
        {value: 'table', icon: 'list'},
    ];

    @ViewChild('contentElement') contentElement?: ElementRef<HTMLElement>;

    @HostBinding('class.inlays') get inlays(): boolean {
        return this.layout === 'inlays';
    }

    @Input() elementId: number = 0;

    constructor(
        private elementsService: ElementsService,
    ) {
        window.addEventListener('popstate', this.historyUpdateHandler.bind(this));
    }

    historyUpdateHandler(event: PopStateEvent): void {
        if (typeof event.state != 'undefined') {
            if (event.state.elementId === this.elementId) {
                this.loading = true;

                this.elementsService.getModel<ZxProdCategoryDto, ZxProdCategory>(this.elementId, ZxProdCategory, event.state.parameters, 'zxProdsList').subscribe(
                    model => {
                        this.model = model;
                        this.pagesAmount = Math.ceil(this.model.prodsAmount / this.elementsOnPage);
                    },
                    () => {
                    },
                    () => {
                        this.loading = false;
                        this.contentElement?.nativeElement.scrollIntoView({
                            block: 'start',
                            inline: 'start',
                            behavior: 'smooth',
                        });
                    },
                );
            }
        }
    }

    ngOnInit(): void {
        this.fetchPrefetchedModel();
    }

    private fetchPrefetchedModel(): void {
        this.elementsService.getPrefetchedModel<ZxProdCategoryDto, ZxProdCategory>(this.elementId, ZxProdCategory).subscribe(
            model => {
                this.model = model;
                this.pagesAmount = Math.ceil(this.model.prodsAmount / this.elementsOnPage);
                this.currentPage = this.model.selectorValues.page;
                this.letter = this.model.selectorValues.letter;
                this.years = this.model.selectorValues.years;
                this.legalStatuses = this.model.selectorValues.statuses;
                // this.tags = this.model.selectorValues.tags;
                this.countries = this.model.selectorValues.countries;
                this.hw = this.model.selectorValues.hw;
                this.formats = this.model.selectorValues.formats;
                this.releaseTypes = this.model.selectorValues.releaseTypes;
                this.languages = this.model.selectorValues.languages;
                this.releases = this.model.selectorValues.releases;
                this.includeSubcategoriesProds = this.model.selectorValues.includeSubcategoriesProds;
                this.sorting = this.model.sortingSelector[0]?.values.find(item => item.selected)?.value;

                let urlBase = this.model.url;
                const parameters = this.gatherParameters();
                for (const [key, value] of Object.entries(parameters)) {
                    if (key !== 'page') {
                        urlBase += `${key}:${value}/`;
                    }
                }

                this.urlBase = urlBase;
            },
            () => {

            },
            () => {
                this.loading = false;
                this.contentElement?.nativeElement.scrollIntoView({
                    block: 'start',
                    inline: 'start',
                    behavior: 'smooth',
                });
            },
        );

    }

    private gatherParameters(): PostParameters {
        const parameters: PostParameters = {};

        if (this.years.length) {
            parameters.years = this.years.join(',');
        }
        if (this.hw.length) {
            parameters.hw = this.hw.join(',');
        }
        if (this.languages.length) {
            parameters.languages = this.languages.join(',');
        }
        if (this.legalStatuses.length) {
            const defaultSet = new Set(defaultStatuses);
            const selectedSet = new Set(this.legalStatuses);

            const areStatusesEqual = defaultSet.size === selectedSet.size &&
                [...defaultSet].every(elem => selectedSet.has(elem));

            if (!areStatusesEqual) {
                parameters.statuses = this.legalStatuses.join(',');
            }
        }
        if (this.formats.length) {
            parameters.formats = this.formats.join(',');
        }
        if (this.releaseTypes.length) {
            parameters.types = this.releaseTypes.join(',');
        }
        if (this.letter) {
            parameters.letter = this.letter;
        }
        if (this.sorting && this.sorting !== 'votes,desc') {
            parameters.sorting = this.sorting;
        }
        if (this.tags.length) {
            parameters.tags = this.tags.join(',');
        }
        if (this.releases) {
            parameters.releases = 1;
        }
        if (!this.includeSubcategoriesProds) {
            parameters.includeSubcategoriesProds = 0;
        }
        if (this.countries.length) {
            parameters.countries = this.countries.join(',');
        }
        if (this.currentPage > 1) {
            parameters.page = this.currentPage;
        }
        return parameters;
    }

    private fetchModel(): void {
        this.loading = true;
        const parameters = this.gatherParameters();
        this.elementsService.getModel<ZxProdCategoryDto, ZxProdCategory>(this.elementId, ZxProdCategory, parameters, 'zxProdsList').subscribe(
            model => {
                this.model = model;
                this.pagesAmount = Math.ceil(this.model.prodsAmount / this.elementsOnPage);
                this.currentPage = this.model.selectorValues.page;
                this.letter = this.model.selectorValues.letter;
                this.years = this.model.selectorValues.years;
                this.legalStatuses = this.model.selectorValues.statuses;
                // this.tags = this.model.selectorValues.tags;
                this.countries = this.model.selectorValues.countries;
                this.hw = this.model.selectorValues.hw;
                this.formats = this.model.selectorValues.formats;
                this.releaseTypes = this.model.selectorValues.releaseTypes;
                this.languages = this.model.selectorValues.languages;
                this.releases = this.model.selectorValues.releases;
                this.includeSubcategoriesProds = this.model.selectorValues.includeSubcategoriesProds;
                this.sorting = this.model.sortingSelector[0]?.values.find(item => item.selected)?.value;

                let reqUrl = this.model.url;
                let urlBase = this.model.url;

                for (const [key, value] of Object.entries(parameters)) {
                    reqUrl += `${key}:${value}/`;

                    if (key !== 'page') {
                        urlBase += `${key}:${value}/`;
                    }
                }

                this.urlBase = urlBase;
                if (environment.production) {
                    if (window.location.href !== reqUrl) {
                        window.history.pushState({parameters, elementId: this.elementId}, '', reqUrl);
                    }
                }
            },
            () => {

            },
            () => {
                this.loading = false;
                this.contentElement?.nativeElement.scrollIntoView({
                    block: 'start',
                    inline: 'start',
                    behavior: 'smooth',
                });
            },
        );
    }

    setCurrentPage(newPage: number): void {
        this.currentPage = newPage;
        this.fetchModel();
    }

    yearsChanged(years: Array<string>) {
        this.years = years;
        this.currentPage = 0;

        this.fetchModel();
    }

    hardwareChanged(hw: Array<string>) {
        this.hw = hw;
        this.currentPage = 0;

        this.fetchModel();
    }

    formatsChanged(formats: Array<string>) {
        this.formats = formats;
        this.currentPage = 0;

        this.fetchModel();
    }

    releaseTypesChanged(releaseTypes: Array<string>) {
        this.releaseTypes = releaseTypes;
        this.currentPage = 0;

        this.fetchModel();
    }

    languagesChanged(languages: Array<string>) {
        this.languages = languages;
        this.currentPage = 0;

        this.fetchModel();
    }

    legalStatusesChanged(legalStatuses: Array<string>) {
        this.legalStatuses = legalStatuses;
        this.currentPage = 0;

        this.fetchModel();
    }

    letterSelected(letter: string) {
        this.letter = letter;
        this.currentPage = 0;

        this.fetchModel();
    }

    sortingSelected(sorting: string) {
        this.sorting = sorting;
        this.currentPage = 0;

        this.fetchModel();
    }

    tagsSelected(tags: Array<Tag>) {
        this.tags = tags.map(tag => tag.id);
        this.currentPage = 0;

        this.fetchModel();
    }

    releasesSelected() {
        this.fetchModel();
    }

    includeSubcategoriesProdsSelected() {
        this.fetchModel();
    }

    countriesChanged(countries: Array<string>) {
        this.countries = countries;
        this.currentPage = 0;

        this.fetchModel();
    }

    categoryChanged(categoryId: number) {
        this.resetSelectors();
        this.elementId = categoryId;
        this.fetchModel();
    }

    tableSortingClicked(type: string) {
        if (this.sorting === type + ',desc') {
            this.sorting = type + ',asc';
        } else {
            this.sorting = type + ',desc';
        }
        this.fetchModel();
    }

    recentPresetActive(): boolean {
        if (this.model?.yearsSelector && this.model.yearsSelector[0]) {
            const currentYear = new Date().getFullYear();
            const selector = this.model.yearsSelector[0];
            const lastValue = selector.values[selector.values.length - 1];
            if (lastValue.value.toString() === currentYear.toString() || lastValue.value.toString() === (currentYear - 1).toString()) {
                return true;
            }
        }
        return false;
    }

    recentPresetClicked(): void {
        this.resetSelectors();
        const currentYear = new Date().getFullYear();
        this.years = [(currentYear - 2).toString(), (currentYear - 1).toString(), currentYear.toString()];
        this.fetchModel();
    }

    yearPresetActive(): boolean {
        if (this.model?.yearsSelector && this.model.yearsSelector[0]) {
            const currentYear = new Date().getFullYear();
            const selector = this.model.yearsSelector[0];
            const lastValue = selector.values[selector.values.length - 1];
            if (lastValue.value.toString() === currentYear.toString()) {
                return true;
            }
        }
        return false;
    }

    yearPresetClicked(): void {
        this.resetSelectors();
        const currentYear = new Date().getFullYear();
        this.years = [currentYear.toString()];
        this.fetchModel();
    }

    topPresetClicked(): void {
        this.resetSelectors();
        this.fetchModel();
    }

    updatesPresetClicked(): void {
        this.resetSelectors();
        this.sorting = 'date,desc';
        this.fetchModel();
    }

    hwPresetActive(hwValues: Array<string>): boolean {
        if (this.model?.hardwareSelector) {
            for (const group of this.model.hardwareSelector) {
                for (const value of group.values) {
                    if (hwValues.indexOf(value.value) >= 0) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    hwPresetClicked(hwValues: Array<string>): void {
        this.resetSelectors();
        this.hw = hwValues;
        this.fetchModel();
    }

    private resetSelectors(): void {
        this.sorting = 'votes,desc';
        this.releases = false;
        this.includeSubcategoriesProds = true;
        this.years = [];
        this.hw = [];
        this.languages = [];
        this.legalStatuses = [];
        this.formats = [];
        this.releaseTypes = [];
        this.letter = '';
        this.tags = [];
        this.countries = [];
        this.currentPage = 0;
    }
}
