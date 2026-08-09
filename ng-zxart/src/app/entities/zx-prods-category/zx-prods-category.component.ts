import {
  ChangeDetectionStrategy,
  ChangeDetectorRef,
  Component,
  ElementRef,
  EventEmitter,
  HostBinding,
  Input,
  OnDestroy,
  OnInit,
  Output,
  ViewChild
} from '@angular/core';
import {ActivatedRoute, Params, Router} from '@angular/router';
import {Subscription} from 'rxjs';
import {ElementsService, PostParameters} from '../../shared/services/elements.service';
import {ZxProdCategory} from './models/zx-prod-category';
import {Tag} from '../../shared/models/tag';
import {ZxProdCategoryDto} from './models/zx-prod-category-dto';
import {CategorySelectorDto} from './models/categories-selector-dto';
import {SelectorDto} from './models/selector-dto';
import {environment} from '../../../environments/environment';
import {TranslatePipe, TranslateService} from '@ngx-translate/core';
import {ZxPaginationComponent} from '../../shared/ui/zx-pagination/zx-pagination.component';
import {
  CategoriesTreeSelectorComponent,
} from './components/categories-tree-selector/categories-tree-selector.component';
import {SortingSelectorComponent} from './components/sorting-selector/sorting-selector.component';
import {DialogSelectorComponent} from './components/dialog-selector/dialog-selector.component';
import {ZxNavChipsComponent} from '../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {ZxNavChip} from '../../shared/ui/zx-nav-chips/nav-chip';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {
  ZxProdsCategorySkeletonComponent,
} from '../../shared/ui/zx-skeleton/components/zx-prods-category-skeleton/zx-prods-category-skeleton.component';
import {ZxProdBlockComponent} from '../zx-prod-block/zx-prod-block.component';
import {ZxProdRowComponent} from '../zx-prod-row/zx-prod-row.component';
import {FormsModule} from '@angular/forms';
import {CommonModule} from '@angular/common';
import {TagsSelectorComponent} from '../../shared/components/tags-selector/tags-selector.component';
import {ZxCheckboxFieldComponent} from '../../shared/ui/zx-checkbox-field/zx-checkbox-field.component';
import {ZxButtonComponent} from '../../shared/ui/zx-button/zx-button.component';
import {ZxToggleComponent, ZxToggleOption} from '../../shared/ui/zx-toggle/zx-toggle.component';
import {SvgIconRegistryService} from 'angular-svg-icon';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {ZxLoadingStateDirective} from '../../shared/ui/zx-loading-state/zx-loading-state.directive';

const defaultStatuses: string[] = ['allowed', 'forbidden', 'forbiddenzxart', 'allowedzxart', 'insales', 'donationware', 'recovered', 'unknown'];

export type ZxProdsListLayout = 'loading' | 'screenshots' | 'inlays' | 'table';

@Component({
    selector: 'zx-prods-category',
    templateUrl: './zx-prods-category.component.html',
    styleUrls: ['./zx-prods-category.component.scss'],
    imports: [
        TranslatePipe,
        ZxPaginationComponent,
        CategoriesTreeSelectorComponent,
        SortingSelectorComponent,
        DialogSelectorComponent,
        ZxNavChipsComponent,
        ZxStackComponent,
        ZxProdBlockComponent,
        ZxProdRowComponent,
        FormsModule,
        CommonModule,
        TagsSelectorComponent,
        ZxProdsCategorySkeletonComponent,
        ZxButtonComponent,
        ZxToggleComponent,
        ZxCheckboxFieldComponent,
        HeadingDirective,
        ZxLoadingStateDirective,
    ],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdsCategoryComponent implements OnInit, OnDestroy {
    public model!: ZxProdCategory;
    /** Language filter, labelled from the SPA's own translations and sorted by them. */
    public languagesSelector: SelectorDto = [];
    /** Hardware filter, labelled from the SPA's own translations. */
    public hardwareSelector: SelectorDto = [];
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
    public paginationQueryParams: Params = {};

    public layoutOptions: ZxToggleOption[] = [
        {value: 'loading', icon: 'image'},
        {value: 'screenshots', icon: 'videogame-asset'},
        {value: 'inlays', icon: 'photo-camera'},
        {value: 'table', icon: 'list'},
    ];

    @ViewChild('contentElement') contentElement?: ElementRef<HTMLElement>;

    @HostBinding('class.inlays') get inlays(): boolean {
        return this.layout === 'inlays';
    }

    @Input() elementId: number = 0;
    /**
     * SPA collection mount point without a hardcoded id: the backend resolves the
     * catalogue root by this structure type. The `cat` query param then overrides
     * it per category.
     */
    @Input() rootType = '';
    /** The routed collection page owns its page-level heading. */
    @Input() showHeading = true;
    /**
     * The selected category chain, root-first and inclusive of the current
     * category (empty at the catalogue root). Emitted after every load so the
     * routed page can render it as breadcrumbs.
     */
    @Output() categoryPathChange = new EventEmitter<CategorySelectorDto[]>();

    /** Catalogue root id; resolved from the loaded root model, or from the `cat` query param. */
    private rootElementId = 0;
    private routerSub?: Subscription;
    private langSub?: Subscription;

    constructor(
        private elementsService: ElementsService,
        private cdr: ChangeDetectorRef,
        private iconReg: SvgIconRegistryService,
        private router: Router,
        private route: ActivatedRoute,
        private translate: TranslateService,
    ) {}

    /** Letter filter rendered as a chip strip; letters come from the backend, `''` is the "all" reset chip. */
    get letterChips(): ZxNavChip[] {
        const selector = this.model?.lettersSelector?.[0];
        if (!selector) {
            return [];
        }
        const chips: ZxNavChip[] = [{
            label: this.translate.instant('prods-list.filters.letters.all'),
            value: '',
            active: !selector.values.some(letter => letter.selected),
        }];
        for (const letter of selector.values) {
            chips.push({label: letter.title, value: letter.value, active: letter.selected});
        }
        return chips;
    }

    ngOnInit(): void {
        for (const name of ['image', 'videogame-asset', 'photo-camera', 'list']) {
            this.iconReg.loadSvg(`${environment.svgUrl}${name}.svg`, name)?.subscribe();
        }
        // Category (`cat`), filters and page all live in the URL query params.
        this.rootElementId = this.elementId;
        this.routerSub = this.route.queryParams.subscribe(params => this.applyQueryParams(params));
        this.langSub = this.translate.onLangChange.subscribe(() => {
            if (this.model) {
                this.buildClientLabelSelectors();
            }
            this.cdr.markForCheck();
        });
    }

    ngOnDestroy(): void {
        this.routerSub?.unsubscribe();
        this.langSub?.unsubscribe();
    }

    /** Reads the full filter state from the URL query params and loads the page. */
    private applyQueryParams(params: Params): void {
        this.elementId = params['cat'] ? +params['cat'] : this.rootElementId;
        this.years = this.splitParam(params['years']);
        this.hw = this.splitParam(params['hw']);
        this.languages = this.splitParam(params['languages']);
        this.legalStatuses = params['statuses'] ? params['statuses'].split(',') : [...defaultStatuses];
        this.formats = this.splitParam(params['formats']);
        this.releaseTypes = this.splitParam(params['types']);
        this.letter = params['letter'] ?? '';
        this.sorting = params['sorting'] ?? 'votes,desc';
        this.tags = params['tags'] ? params['tags'].split(',').map(Number) : [];
        this.countries = this.splitParam(params['countries']);
        this.releases = params['releases'] === '1';
        this.includeSubcategoriesProds = params['includeSubcategoriesProds'] !== '0';
        this.currentPage = params['page'] ? +params['page'] : 1;
        this.loadData();
    }

    private splitParam(value: string | undefined): string[] {
        return value ? value.split(',') : [];
    }

    /** Pushes the current filter state to the URL as query params. */
    private navigateWithParams(): void {
        const queryParams = this.buildRouteQueryParams(true);
        this.router.navigate([], {relativeTo: this.route, queryParams});
    }

    /**
     * The categories selector marks the whole ancestor chain of the current
     * category as selected, so descending through the selected nodes yields the
     * root→current path without asking the backend for it.
     */
    private selectedCategoryPath(): CategorySelectorDto[] {
        const path: CategorySelectorDto[] = [];
        let level: CategorySelectorDto[] | undefined = this.model.categoriesSelector;
        while (level?.length) {
            const selected: CategorySelectorDto | undefined = level.find(category => category.selected);
            if (!selected) {
                break;
            }
            path.push(selected);
            level = selected.children;
        }
        return path;
    }

    private buildRouteQueryParams(includePage: boolean): Params {
        const queryParams: Params = {...this.gatherParameters()};
        if (!includePage) {
            delete queryParams['page'];
        }
        if (this.elementId !== this.rootElementId) {
            queryParams['cat'] = this.elementId;
        }
        return queryParams;
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

    /** Writes the state to the URL; the queryParams subscription reloads. */
    private fetchModel(): void {
        this.navigateWithParams();
    }

    /**
     * The backend sends bare language codes; the names live in the SPA's i18n,
     * so both the labels and their alphabetical order are decided here.
     */
    private buildLanguagesSelector(selector: SelectorDto): SelectorDto {
        return selector.map(group => ({
            ...group,
            values: group.values
                .map(item => ({...item, title: this.translate.instant(`language.${item.value}`)}))
                .sort((a, b) => a.title.localeCompare(b.title, this.translate.currentLang ?? undefined)),
        }));
    }

    /**
     * Item names arrive from the backend catalog already localized; only the
     * group name is still a code the SPA translates.
     */
    private buildHardwareSelector(selector: SelectorDto): SelectorDto {
        return selector.map(group => ({
            ...group,
            title: this.translate.instant(`hardware-group.${group.title}`),
        }));
    }

    private buildClientLabelSelectors(): void {
        this.languagesSelector = this.buildLanguagesSelector(this.model.languagesSelector);
        this.hardwareSelector = this.buildHardwareSelector(this.model.hardwareSelector);
    }

    private loadData(): void {
        this.loading = true;
        const parameters = this.gatherParameters();
        this.elementsService.getModel<ZxProdCategoryDto, ZxProdCategory>(this.elementId, ZxProdCategory, parameters, 'zxProdsList', this.rootType).subscribe(
            model => {
                this.model = model;
                this.buildClientLabelSelectors();
                // Type-resolved root: adopt its real id so category navigation and the
                // `cat != root` URL check work without a hardcoded wrapper id.
                if (model.id) {
                    if (!this.rootElementId) {
                        this.rootElementId = model.id;
                    }
                    if (!this.elementId) {
                        this.elementId = model.id;
                    }
                }
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

                this.paginationQueryParams = this.buildRouteQueryParams(false);
                this.categoryPathChange.emit(this.selectedCategoryPath());
            },
            () => {

            },
            () => {
                this.loading = false;
                this.cdr.markForCheck();
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
