import {ChangeDetectionStrategy, ChangeDetectorRef, Component, ElementRef, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {AuthorProdEntry, AuthorProdItem, AuthorReleaseEntry, AuthorProdsApiService} from '../../services/author-prods-api.service';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxChipComponent, ZxChipColor} from '../../../../shared/ui/zx-chip/zx-chip.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxProdsGridDirective} from '../../../../shared/directives/prods-grid.directive';
import {ZxProdReleaseCardComponent} from '../../../prod-details/components/zx-prod-release-card/zx-prod-release-card.component';
import {scrollToElementIfHidden} from '../../scroll-to-tabs';

const PAGE_SIZE = 15;

interface YearGroup {
  year: number | null;
  prods: AuthorProdItem[];
}

@Component({
  selector: 'zx-author-software-tab',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPaginationComponent,
    ZxProdBlockComponent,
    ZxFilterBarComponent,
    ZxButtonControlsComponent,
    ZxButtonComponent,
    ZxPanelComponent,
    ZxStackComponent,
    ZxProdsListSkeletonComponent,
    ZxChipComponent,
    TextDirective,
    ZxInlineComponent,
    ZxProdsGridDirective,
    ZxProdReleaseCardComponent,
  ],
  templateUrl: './zx-author-software-tab.component.html',
  styleUrl: './zx-author-software-tab.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorSoftwareTabComponent implements OnInit, OnDestroy {
  @Input() elementId = 0;
  @Input() urlBase = '';

  private readonly roleStore = new BehaviorSubject<string>('');
  private readonly sortStore = new BehaviorSubject<string>('year-desc');
  private pageStore = new BehaviorSubject<number>(1);

  error = false;
  loading = true;
  total = 0;
  yearGroups: YearGroup[] = [];
  availableRoles: string[] = [];

  private readonly subscriptions = new Subscription();

  get activeRole(): string { return this.roleStore.getValue(); }
  get currentSort(): string { return this.sortStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pagesAmount(): number { return Math.ceil(this.total / PAGE_SIZE); }

  constructor(
    private readonly prodsApiService: AuthorProdsApiService,
    private readonly cdr: ChangeDetectorRef,
    private readonly element: ElementRef<HTMLElement>,
  ) {}

  ngOnInit(): void {
    this.pageStore = new BehaviorSubject<number>(this.parsePageFromUrl());
    this.subscriptions.add(
      combineLatest([this.roleStore, this.sortStore, this.pageStore]).pipe(
        switchMap(([role, sort, page]) => {
          this.loading = true;
          this.cdr.markForCheck();
          const {sortKey, sortDir} = this.parseSortKey(sort);
          const start = (page - 1) * PAGE_SIZE;
          return this.prodsApiService.getProds(this.elementId, start, PAGE_SIZE, sortKey, sortDir, role);
        }),
      ).subscribe({
        next: result => {
          this.loading = false;
          this.total = result.total;
          this.availableRoles = result.availableRoles;
          this.yearGroups = this.buildGroups(result.items, this.sortStore.getValue());
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

  setRole(role: string): void {
    this.roleStore.next(role);
    this.pageStore.next(1);
  }

  setSort(sort: string): void {
    this.sortStore.next(sort);
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

  getVisibleRoles(roles: string[]): string[] {
    return roles.filter(r => r !== 'unknown');
  }

  getRoleLabelKey(role: string): string {
    return `prod-details.role_${role}`;
  }

  getRoleChipColor(role: string): ZxChipColor {
    if (role === 'music') return 'primary';
    if (role === 'code') return 'code';
    if (role === 'intro' || role === 'design') return 'intro';
    return 'artist';
  }

  isProdEntry(item: AuthorProdItem): item is AuthorProdEntry {
    return item.type === 'prod';
  }

  toProdModel(item: AuthorProdEntry): ZxProd {
    return new ZxProd(item);
  }

  asRelease(item: AuthorProdItem): AuthorReleaseEntry {
    return item as AuthorReleaseEntry;
  }

  asProd(item: AuthorProdItem): AuthorProdEntry {
    return item as AuthorProdEntry;
  }

  private parseSortKey(sort: string): {sortKey: string; sortDir: string} {
    if (sort === 'year-asc') return {sortKey: 'year', sortDir: 'asc'};
    if (sort === 'year-desc') return {sortKey: 'year', sortDir: 'desc'};
    if (sort === 'downloads') return {sortKey: 'downloads', sortDir: 'desc'};
    if (sort === 'plays') return {sortKey: 'plays', sortDir: 'desc'};
    return {sortKey: 'votes', sortDir: 'desc'};
  }

  private getItemYear(item: AuthorProdItem): number {
    if (item.type === 'release') {
      return item.year ?? 0;
    }
    return item.year ? Number(item.year) : 0;
  }

  private buildGroups(items: AuthorProdItem[], sort: string): YearGroup[] {
    if (sort === 'votes' || sort === 'downloads' || sort === 'plays') {
      return [{year: null, prods: items}];
    }
    const byYear = new Map<number, AuthorProdItem[]>();
    for (const item of items) {
      const year = this.getItemYear(item);
      const list = byYear.get(year) ?? [];
      list.push(item);
      byYear.set(year, list);
    }
    const dir = sort === 'year-asc' ? 1 : -1;
    return Array.from(byYear.entries())
      .map(([year, prods]) => ({year, prods}))
      .sort((a, b) => dir * ((a.year ?? 0) - (b.year ?? 0)));
  }
}
