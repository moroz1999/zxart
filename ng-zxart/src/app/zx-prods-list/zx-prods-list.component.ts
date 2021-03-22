import {Component, OnInit, Input} from '@angular/core';
import {ElementsService} from '../shared/services/elements.service';
import {ZxProdsList} from './models/zx-prods-list.model';
import {ZxProdCategoryResponseDto} from './models/zx-prod-category-response-dto';

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

  @Input() elementId: number = 0;

  constructor(
    private elementsService: ElementsService,
  ) {
  }

  ngOnInit(): void {
    this.fetchModel();
  }

  private fetchModel(): void {
    const parameters = {
      elementsOnPage: this.elementsOnPage,
      page: this.currentPage,
    };
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
}
