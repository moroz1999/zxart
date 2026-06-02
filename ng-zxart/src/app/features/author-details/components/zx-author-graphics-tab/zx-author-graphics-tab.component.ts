import {ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {AuthorPicturesService} from '../../../author-pictures/services/author-pictures.service';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxPicturesGridDirective} from '../../../../shared/directives/pictures-grid.directive';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxPictureGridSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {PictureGalleryHostComponent} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

const PAGE_SIZE = 24;

interface YearGroup {
  year: string | null;
  pictures: ZxPictureDto[];
  startIndex: number;
}

@Component({
  selector: 'zx-author-graphics-tab',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxPaginationComponent,
    ZxPicturesGridDirective,
    ZxFilterBarComponent,
    ZxButtonControlsComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxPictureGridSkeletonComponent,
    TextDirective,
    PictureGalleryHostComponent,
  ],
  templateUrl: './zx-author-graphics-tab.component.html',
  styleUrl: './zx-author-graphics-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorGraphicsTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() urlBase = '';

  private readonly sortStore = new BehaviorSubject<string>('year-desc');
  private readonly formatStore = new BehaviorSubject<string>('');
  private pageStore = new BehaviorSubject<number>(1);

  error = false;
  loading = true;
  total = 0;
  yearGroups: YearGroup[] = [];
  availableFormats: string[] = [];

  private readonly galleryId = 'author-graphics';
  private readonly subscriptions = new Subscription();

  get currentSort(): string { return this.sortStore.getValue(); }
  get currentFormat(): string { return this.formatStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pagesAmount(): number { return Math.ceil(this.total / PAGE_SIZE); }
  get galleryIdValue(): string { return this.galleryId; }

  constructor(
    private readonly authorPicturesService: AuthorPicturesService,
    private readonly pictureGalleryService: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
    private readonly element: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    this.pageStore = new BehaviorSubject<number>(this.parsePageFromUrl());
    this.subscriptions.add(
      combineLatest([this.sortStore, this.formatStore, this.pageStore]).pipe(
        switchMap(([sort, format, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {column, dir} = this.parseSortKey(sort);
          const start = (page - 1) * PAGE_SIZE;
          return this.authorPicturesService.getPicturesPaged(this.elementId, start, PAGE_SIZE, column, dir, format);
        }),
      ).subscribe({
        next: result => {
          this.loading = false;
          this.total = result.total;
          this.yearGroups = this.buildGroups(result.items, this.sortStore.getValue());
          if (result.availableFormats?.length) {
            this.availableFormats = result.availableFormats;
          }
          this.pictureGalleryService.ensureGalleryLoaded(this.galleryId, result.items);
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.error = true;
          this.cdr.markForCheck();
        },
      }),
    );
  }

  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  setSort(sort: string): void {
    this.sortStore.next(sort);
    this.pageStore.next(1);
  }

  setFormat(format: string): void {
    this.formatStore.next(format);
    this.pageStore.next(1);
  }

  onPageChange(page: number): void {
    this.pageStore.next(page);
    this.pushPageToUrl(page);
    scrollToElementIfHidden(this.element.nativeElement.closest('zx-tabs'));
  }

  private parsePageFromUrl(): number {
    const match = window.location.pathname.match(/\/page:(\d+)/);
    const page = match ? parseInt(match[1], 10) : 1;
    return page > 0 ? page : 1;
  }

  private pushPageToUrl(page: number): void {
    if (!this.urlBase) return;
    const newUrl = page > 1 ? this.urlBase + 'page:' + page + '/' : this.urlBase;
    window.history.pushState(null, '', newUrl);
  }

  private parseSortKey(sort: string): {column: string; dir: string} {
    if (sort === 'year-asc') return {column: 'year', dir: 'asc'};
    if (sort === 'year-desc') return {column: 'year', dir: 'desc'};
    if (sort === 'views') return {column: 'views', dir: 'desc'};
    return {column: 'votes', dir: 'desc'};
  }

  private buildGroups(pictures: ZxPictureDto[], sort: string): YearGroup[] {
    if (sort === 'votes' || sort === 'views') {
      return [{year: null, pictures, startIndex: 0}];
    }
    const byYear = new Map<string, ZxPictureDto[]>();
    for (const pic of pictures) {
      const year = pic.year ?? 'Unknown';
      const list = byYear.get(year) ?? [];
      list.push(pic);
      byYear.set(year, list);
    }
    const groups: YearGroup[] = [];
    let startIndex = 0;
    const sorted = Array.from(byYear.entries()).sort(([a], [b]) =>
      sort === 'year-asc' ? Number(a) - Number(b) : Number(b) - Number(a),
    );
    for (const [year, pics] of sorted) {
      groups.push({year, pictures: pics, startIndex});
      startIndex += pics.length;
    }
    return groups;
  }
}
