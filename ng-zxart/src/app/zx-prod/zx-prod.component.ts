import {Component, Input, OnInit} from '@angular/core';
import {ZxProd} from '../zx-prods-list/models/zx-prod';

@Component({
  selector: 'app-zx-prod',
  templateUrl: './zx-prod.component.html',
  styleUrls: ['./zx-prod.component.scss'],
})
export class ZxProdComponent implements OnInit {
  @Input() model!: ZxProd;

  constructor() {
  }

  ngOnInit(): void {
  }

}
