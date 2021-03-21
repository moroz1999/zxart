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

  @Input() elementId: number = 0;

  constructor(
    private elementsService: ElementsService,
  ) {
  }

  ngOnInit(): void {
    this.fetchModel();
  }

  private fetchModel(): void {

    this.elementsService.getModel<ZxProdCategoryResponseDto, ZxProdsList>(this.elementId, ZxProdsList).subscribe(
      model => {
        this.model = model;
      },
    );
  }


  setCurrentPage(newPage: number): void {
    this.currentPage = newPage;
    this.fetchModel();
  }
}
