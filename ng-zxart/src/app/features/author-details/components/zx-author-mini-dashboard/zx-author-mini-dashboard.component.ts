import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {forkJoin, of} from 'rxjs';
import {catchError} from 'rxjs/operators';
import {AuthorTabsDto} from '../../models/author-core.dto';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {AuthorProdDto} from '../../models/author-prod.dto';
import {AuthorPicturesService} from '../../../author-pictures/services/author-pictures.service';
import {AuthorTunesService} from '../../../author-tunes/services/author-tunes.service';
import {AuthorProdsApiService} from '../../services/author-prods-api.service';
import {ZxPictureCardComponent} from '../../../../shared/ui/zx-picture-card/zx-picture-card.component';
import {ZxTuneRowComponent} from '../../../../shared/ui/zx-tune-row/zx-tune-row.component';
import {ZxProdBlockComponent} from '../../../../shared/ui/zx-prod-block/zx-prod-block.component';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {ZxProdDto} from '../../../../shared/models/zx-prod-dto';

const DASHBOARD_SIZE = 4;

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
  selector: 'zx-author-mini-dashboard',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPictureCardComponent,
    ZxTuneRowComponent,
    ZxProdBlockComponent,
    ZxPanelComponent,
  ],
  templateUrl: './zx-author-mini-dashboard.component.html',
  styleUrl: './zx-author-mini-dashboard.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxAuthorMiniDashboardComponent implements OnInit {
  @Input() elementId = 0;
  @Input() tabs!: AuthorTabsDto;

  pictures: ZxPictureDto[] = [];
  tunes: ZxTuneDto[] = [];
  prods: AuthorProdDto[] = [];
  loading = true;
  playingTuneId: number | null = null;

  gfxHref = '';
  musicHref = '';
  softwareHref = '';

  constructor(
    private readonly picturesService: AuthorPicturesService,
    private readonly tunesService: AuthorTunesService,
    private readonly prodsService: AuthorProdsApiService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  ngOnInit(): void {
    const baseUrl = this.parseBaseUrl();
    this.gfxHref = baseUrl + 'tab:gfx/';
    this.musicHref = baseUrl + 'tab:music/';
    this.softwareHref = baseUrl + 'tab:software/';

    const pics$ = this.tabs.hasPictures
      ? this.picturesService.getPicturesPaged(this.elementId, 0, DASHBOARD_SIZE, 'votes', 'desc').pipe(
          catchError(() => of({items: [], total: 0, availableFormats: []})),
        )
      : of({items: [], total: 0, availableFormats: []});

    const tunes$ = this.tabs.hasTunes
      ? this.tunesService.getTunesPaged(this.elementId, 0, DASHBOARD_SIZE, 'votes', 'desc').pipe(
          catchError(() => of({items: [], total: 0, availableFormats: []})),
        )
      : of({items: [], total: 0, availableFormats: []});

    const prods$ = this.tabs.hasProds
      ? this.prodsService.getProds(this.elementId, 0, DASHBOARD_SIZE, 'votes', 'desc', '').pipe(
          catchError(() => of({items: [], total: 0, availableRoles: []})),
        )
      : of({items: [], total: 0, availableRoles: []});

    forkJoin([pics$, tunes$, prods$]).subscribe(([picPage, tunePage, prodPage]) => {
      this.pictures = picPage.items;
      this.tunes = tunePage.items;
      this.prods = prodPage.items;
      this.loading = false;
      this.cdr.markForCheck();
    });
  }

  get hasContent(): boolean {
    return this.pictures.length > 0 || this.tunes.length > 0 || this.prods.length > 0;
  }

  toProdModel(dto: AuthorProdDto): ZxProd {
    return authorProdToZxProd(dto);
  }

  onPlayRequested(tune: ZxTuneDto): void {
    this.playingTuneId = tune.id;
    this.cdr.markForCheck();
  }

  onPauseRequested(): void {
    this.playingTuneId = null;
    this.cdr.markForCheck();
  }

  private parseBaseUrl(): string {
    let path = window.location.pathname;
    path = path.replace(/\/tab:[^/]+/g, '');
    path = path.replace(/\/page:\d+/g, '');
    return path.endsWith('/') ? path : path + '/';
  }
}
