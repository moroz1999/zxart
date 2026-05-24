import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnDestroy, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Subscription, switchMap} from 'rxjs';
import {AuthorProdDto} from '../../models/author-prod.dto';
import {AuthorProdsApiService} from '../../services/author-prods-api.service';
import {ZxPaginationComponent} from '../../../../shared/ui/zx-pagination/zx-pagination.component';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxProdDto} from '../../../../shared/models/zx-prod-dto';
import {ZxFilterBarComponent} from '../../../../shared/ui/zx-filter-bar/zx-filter-bar.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';

const PAGE_SIZE = 12;
const ROLE_LABELS: Record<string, string> = {
  music: 'author-details.software-tab.roles.music',
  gfx: 'author-details.software-tab.roles.gfx',
  code: 'author-details.software-tab.roles.code',
  design: 'author-details.software-tab.roles.design',
  sfx: 'author-details.software-tab.roles.sfx',
};

interface YearGroup {
  year: number | null;
  prods: AuthorProdDto[];
}

function authorProdToZxProd(dto: AuthorProdDto): ZxProd {
  const data: ZxProdDto = {
    id: dto.id,
    url: dto.url,
    title: dto.title,
    structureType: dto.type === 'release' ? 'zxRelease' : 'zxProd',
    dateCreated: 0,
    year: dto.year > 0 ? String(dto.year) : undefined,
    listImagesUrls: dto.thumbnailUrl ? [dto.thumbnailUrl] : [],
    authorsInfoShort: dto.coAuthors.map(co => ({title: co.name, url: co.url, roles: []})),
    categoriesInfo: dto.category ? [{id: 0, title: dto.category, url: ''}] : [],
    votes: dto.votes,
    votesAmount: dto.votesAmount,
    userVote: 0,
    denyVoting: false,
    legalStatus: 'unknown',
    externalLink: '',
  };
  return new ZxProd(data);
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

  readonly availableRoles: Array<{key: string; labelKey: string}> = Object.entries(ROLE_LABELS)
    .map(([key, labelKey]) => ({key, labelKey}));

  private readonly subscriptions = new Subscription();

  get activeRole(): string { return this.roleStore.getValue(); }
  get currentSort(): string { return this.sortStore.getValue(); }
  get currentPage(): number { return this.pageStore.getValue(); }
  get pagesAmount(): number { return Math.ceil(this.total / PAGE_SIZE); }

  constructor(
    private readonly prodsApiService: AuthorProdsApiService,
    private readonly cdr: ChangeDetectorRef,
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

  getRoleLabelKey(role: string): string {
    return ROLE_LABELS[role] ?? role;
  }

  toProdModel(dto: AuthorProdDto): ZxProd {
    return authorProdToZxProd(dto);
  }

  private parseSortKey(sort: string): {sortKey: string; sortDir: string} {
    if (sort === 'year-asc') return {sortKey: 'year', sortDir: 'asc'};
    if (sort === 'year-desc') return {sortKey: 'year', sortDir: 'desc'};
    return {sortKey: 'votes', sortDir: 'desc'};
  }

  private buildGroups(prods: AuthorProdDto[], sort: string): YearGroup[] {
    if (sort === 'votes') {
      return [{year: null, prods}];
    }
    const byYear = new Map<number, AuthorProdDto[]>();
    for (const prod of prods) {
      const list = byYear.get(prod.year) ?? [];
      list.push(prod);
      byYear.set(prod.year, list);
    }
    return Array.from(byYear.entries())
      .map(([year, prods]) => ({year, prods}))
      .sort((a, b) => (b.year ?? 0) - (a.year ?? 0));
  }
}
