import {Component, OnInit, Input} from '@angular/core';
import {ElementsService, PostParameters} from '../shared/services/elements.service';
import {ZxProdsList} from './models/zx-prods-list.model';
import {ZxProdCategoryResponseDto} from './models/zx-prod-category-response-dto';
import {TranslateService} from '@ngx-translate/core';

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
  public letter?: string;
  public sorting?: string;

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
    if (this.letter) {
      parameters.letter = this.letter;
    }
    if (this.sorting) {
      parameters.sorting = this.sorting;
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
    this.fetchModel();
  }

  letterSelected(letter: string) {
    this.letter = letter;
    this.fetchModel();
  }

  sortingSelected(sorting: string) {
    this.sorting = sorting;
    this.fetchModel();
  }
}
