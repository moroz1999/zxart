import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ZxProdComponent} from '../../shared/components/zx-prod-component';
import {DatePipe, NgForOf, NgIf} from '@angular/common';


import {RouterLink} from '@angular/router';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';

@Component({
    selector: 'zx-prod-row',
    templateUrl: './zx-prod-row.component.html',
    styleUrls: ['./zx-prod-row.component.scss'],
    standalone: true,
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        RouterLink,
        DatePipe,
        NgForOf,
        NgIf,
        TextDirective,
    ],
})
export class ZxProdRowComponent extends ZxProdComponent implements OnInit {
    constructor() {
        super();
    }

    ngOnInit(): void {
    }
}
