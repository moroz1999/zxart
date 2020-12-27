import {Component, OnInit, Input} from '@angular/core';
import {ElementsService} from '../core/services/elements.service';
import {ZxProdsList} from '../models/zx-prods-list.model';

@Component({
  selector: 'app-zx-prods-list',
  templateUrl: './zx-prods-list.component.html',
  styleUrls: ['./zx-prods-list.component.scss']
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
    this.elementsService.getModel(this.elementId).subscribe(
      model => {
        this.model = model;
      }
    );
  }
}
