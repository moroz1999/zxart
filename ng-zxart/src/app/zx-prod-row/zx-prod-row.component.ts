import {Component, OnInit} from '@angular/core';
import {ZxProdComponent} from '../shared/components/zx-prod-component';

@Component({
    selector: 'app-zx-prod-row',
    templateUrl: './zx-prod-row.component.html',
    styleUrls: ['./zx-prod-row.component.scss'],
})
export class ZxProdRowComponent extends ZxProdComponent implements OnInit {
    constructor() {
        super();
    }

    ngOnInit(): void {
    }
}
