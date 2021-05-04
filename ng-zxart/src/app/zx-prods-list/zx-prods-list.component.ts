import {Component, OnInit, Input} from '@angular/core';
import {ElementsService, PostParameters} from '../shared/services/elements.service';
import {ZxProdsList} from './models/zx-prods-list.model';
import {ZxProdCategoryResponseDto} from './models/zx-prod-category-response-dto';
import {TranslateService} from '@ngx-translate/core';
import {Tag} from '../shared/models/tag';

@Component({
  selector: 'app-zx-prods-list',
  templateUrl: './zx-prods-list.component.html',
  styleUrls: ['./zx-prods-list.component.scss'],
})
export class ZxProdsListComponent implements OnInit {
  public model?: ZxProdsList;
  public pagesAmount = 0;
  public currentPage = 1;
  public elementsOnPage = 100;
  public years: Array<string> = [];
  public hw: Array<string> = [];
  public languages: Array<string> = [];
  public legalStatuses: Array<string> = [];
  public formats: Array<string> = [];
  public letter?: string;
  public sorting?: string;
  public tags: Array<number> = [];
  public countries: Array<string> = [];

  @Input() elementId: number = 0;

  constructor(
    public translate: TranslateService,
    private elementsService: ElementsService,
  ) {
  }

  ngOnInit(): void {
    this.translate.addLangs(['en']);
    this.translate.setDefaultLang('en');
    this.fetchModel();
  }

  private fetchModel(): void {
    const parameters: PostParameters = {
      elementsOnPage: this.elementsOnPage,
      page: this.currentPage,
    };
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
      parameters.statuses = this.legalStatuses.join(',');
    }
    if (this.formats.length) {
      parameters.formats = this.formats.join(',');
    }
    if (this.letter) {
      parameters.letter = this.letter;
    }
    if (this.sorting) {
      parameters.sorting = this.sorting;
    }
    if (this.tags.length) {
      parameters.tags = this.tags.join(',');
    }
    if (this.countries.length) {
      parameters.countries = this.countries.join(',');
    }
    this.elementsService.getModel<ZxProdCategoryResponseDto, ZxProdsList>(this.elementId, ZxProdsList, parameters).subscribe(
      model => {
        this.model = model;
        this.pagesAmount = Math.ceil(this.model.prodsAmount / this.elementsOnPage);
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

  countriesChanged(countries: Array<string>) {
    this.countries = countries;
    this.currentPage = 0;

    this.fetchModel();
  }

  categoryChanged(categoryId: number) {
    this.elementId = categoryId;

    this.years = [];
    this.hw = [];
    this.languages = [];
    this.legalStatuses = [];
    this.formats = [];
    this.letter = '';
    this.tags = [];
    this.countries = [];
    this.currentPage = 0;

    this.fetchModel();
  }
}
