import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {PageItemInterface} from './page-item.interface';
import {NgForOf, NgIf} from '@angular/common';
import {ZxButtonComponent} from '../zx-button/zx-button.component';
import {ZxSpinnerComponent} from '../zx-spinner/zx-spinner.component';

@Component({
    selector: 'zx-pagination',
    templateUrl: './zx-pagination.component.html',
    styleUrls: ['./zx-pagination.component.scss'],
    standalone: true,
    imports: [
        NgIf,
        NgForOf,
        ZxButtonComponent,
        ZxSpinnerComponent,
    ],
})
export class ZxPaginationComponent implements OnChanges {
    @Input() currentPage = 0;
    @Input() pagesAmount = 0;
    @Input() visibleAmount = 1;
    @Input() urlBase = '';
    @Input() loading = false;
    @Output() pageChange = new EventEmitter<number>();

    pageItems: PageItemInterface[] = [];

    ngOnChanges(): void {
        if (this.currentPage > this.pagesAmount) {
            this.currentPage = this.pagesAmount;
        } else if (this.currentPage < 1) {
            this.currentPage = 1;
        }

        let start = this.currentPage - this.visibleAmount;
        let end = this.currentPage + this.visibleAmount;

        if (this.currentPage <= this.visibleAmount + 2) {
            end = this.visibleAmount * 2 + 3;
        }

        if (this.currentPage >= this.pagesAmount - this.visibleAmount - 2) {
            start = this.pagesAmount - this.visibleAmount * 2 - 2;
        }

        if (start < 1) {
            start = 1;
        }
        if (end > this.pagesAmount) {
            end = this.pagesAmount;
        }

        this.pageItems = [];
        const pageType = 'page' as const;
        const dotsType = 'dots' as const;

        if (start > 1) {
            this.pageItems.push({number: 1, type: pageType, text: '1'});
        }
        if (start > 2) {
            this.pageItems.push({number: 0, type: dotsType, text: '...'});
        }

        for (let i = start; i <= end; i++) {
            this.pageItems.push({number: i, type: pageType, text: i.toString()});
        }

        if (end < this.pagesAmount - 1) {
            this.pageItems.push({number: 0, type: dotsType, text: '...'});
        }
        if (end < this.pagesAmount) {
            this.pageItems.push({number: this.pagesAmount, type: pageType, text: this.pagesAmount.toString()});
        }
    }

    pageClicked(event: MouseEvent, newPageNumber: number): void {
        event.stopPropagation();
        event.preventDefault();
        if (newPageNumber > 0 && newPageNumber <= this.pagesAmount) {
            this.pageChange.emit(newPageNumber);
        }
    }

    previousPageActive(): boolean {
        return this.currentPage > 1;
    }

    nextPageActive(): boolean {
        return this.currentPage < this.pagesAmount;
    }

    makeHref(number: number): string {
        if (this.urlBase.slice(-1) === '/') {
            return this.urlBase + 'page:' + number;
        }
        return this.urlBase + '/page:' + number;
    }
}
