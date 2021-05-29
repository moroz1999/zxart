import {Component, Input, OnInit} from '@angular/core';
import {ZxProdsList} from './models/zx-prods-list';
import {ElementsService, PostParameters} from '../shared/services/elements.service';
import {ZxProdsListDto} from './models/zx-prods-list-dto';

@Component({
  selector: 'app-zx-prods-list',
  templateUrl: './zx-prods-list.component.html',
  styleUrls: ['./zx-prods-list.component.scss'],
})
export class ZxProdsListComponent implements OnInit {
  public model?: ZxProdsList;
  @Input() elementId: number = 0;

  constructor(
    private elementsService: ElementsService,
  ) {
  }

  ngOnInit(): void {
    this.fetchModel();
  }

  private fetchModel(): void {
    const parameters: PostParameters = {};
    this.elementsService.getModel<ZxProdsListDto, ZxProdsList>(this.elementId, ZxProdsList, parameters, 'zxProdsList').subscribe(
      model => {
        this.model = model;
      },
    );
  }
}
