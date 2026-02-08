import {Component, OnInit} from '@angular/core';
import {ZxProdComponent} from '../shared/components/zx-prod-component';
import {DatePipe, NgForOf, NgIf} from '@angular/common';

@Component({
    selector: 'zx-prod-row',
    templateUrl: './zx-prod-row.component.html',
    styleUrls: ['./zx-prod-row.component.scss'],
    standalone: true,
    imports: [
        DatePipe,
        NgForOf,
        NgIf,
        NgIf,
        NgIf,
        NgForOf,
        NgForOf,
    ],
})
export class ZxProdRowComponent extends ZxProdComponent implements OnInit {
    constructor() {
        super();
    }

    ngOnInit(): void {
    }
}
