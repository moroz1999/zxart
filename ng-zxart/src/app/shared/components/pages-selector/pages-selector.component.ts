import {Component, EventEmitter, Input, OnChanges, Output} from '@angular/core';
import {PageItemInterface} from './page-item-interface';

@Component({
    selector: 'app-pages-selector',
    templateUrl: './pages-selector.component.html',
    styleUrls: ['./pages-selector.component.scss'],
})
export class PagesSelectorComponent implements OnChanges {
    @Input() currentPage = 0;
    @Input() pagesAmount = 0;
    @Input() visibleAmount = 1;
    @Input() urlBase = '';
    @Output() clickCallback: EventEmitter<any> = new EventEmitter();
    pageItems: Array<PageItemInterface> = [];

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
            const page = {
                number: 1,
                type: pageType,
                text: '1',
            };
            this.pageItems.push(page);
        }
        if (start > 2) {
            const page = {
                number: 0,
                type: dotsType,
                text: '...',
            };
            this.pageItems.push(page);
        }

        for (let i = start; i <= end; i++) {
            const page = {
                number: i,
                type: pageType,
                text: i.toString(),
            };
            this.pageItems.push(page);
        }

        if (end < this.pagesAmount - 1) {
            const page = {
                number: 0,
                type: dotsType,
                text: '...',
            };
            this.pageItems.push(page);
        }
        if (end < this.pagesAmount) {
            const page = {
                number: this.pagesAmount,
                type: pageType,
                text: this.pagesAmount.toString(),
            };
            this.pageItems.push(page);
        }
    }

    pageClicked(event: MouseEvent, newPageNumber: number): void {
        event.stopPropagation();
        event.preventDefault();
        if (newPageNumber > 0 && newPageNumber <= this.pagesAmount) {
            this.clickCallback.emit(newPageNumber);
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
