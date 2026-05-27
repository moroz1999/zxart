import {Injectable} from '@angular/core';
import {BehaviorSubject, Observable, filter, forkJoin, map, of, shareReplay, startWith, switchMap} from 'rxjs';
import {AuthorTabsDto} from '../models/author-core.dto';
import {AuthorProdItem} from './author-prods-api.service';
import {ZxPictureDto} from '../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../shared/models/zx-tune-dto';
import {AuthorPicturesService} from '../../author-pictures/services/author-pictures.service';
import {AuthorTunesService} from '../../author-tunes/services/author-tunes.service';
import {AuthorProdsApiService} from './author-prods-api.service';

const DASHBOARD_PICS = 2;
const DASHBOARD_TUNES = 10;
const DASHBOARD_PRODS = 2;
const DASHBOARD_EXPANDED_CARDS = 4;

interface AuthorMiniDashboardContext {
  elementId: number;
  tabs: AuthorTabsDto;
}

export interface AuthorMiniDashboardData {
  loading: boolean;
  pictures: ZxPictureDto[];
  tunes: ZxTuneDto[];
  prods: AuthorProdItem[];
}

@Injectable()
export class AuthorMiniDashboardService {
  private readonly contextStore = new BehaviorSubject<AuthorMiniDashboardContext | null>(null);

  readonly data$: Observable<AuthorMiniDashboardData> = this.contextStore.pipe(
    filter((context): context is AuthorMiniDashboardContext => context !== null),
    switchMap(context => {
      const twoSectionLayout =
        Number(context.tabs.hasPictures) + Number(context.tabs.hasTunes) + Number(context.tabs.hasProds) === 2;
      const picturesLimit = twoSectionLayout && context.tabs.hasPictures ? DASHBOARD_EXPANDED_CARDS : DASHBOARD_PICS;
      const prodsLimit = twoSectionLayout && context.tabs.hasProds ? DASHBOARD_EXPANDED_CARDS : DASHBOARD_PRODS;

      const pictures$ = context.tabs.hasPictures
        ? this.picturesService.getPicturesPaged(context.elementId, 0, picturesLimit, 'votes', 'desc')
        : of({items: [] as ZxPictureDto[], total: 0, availableFormats: []});
      const tunes$ = context.tabs.hasTunes
        ? this.tunesService.getTunesPaged(context.elementId, 0, DASHBOARD_TUNES, 'votes', 'desc')
        : of({items: [] as ZxTuneDto[], total: 0, availableFormatGroups: []});
      const prods$ = context.tabs.hasProds
        ? this.prodsService.getProds(context.elementId, 0, prodsLimit, 'votes', 'desc', '')
        : of({items: [] as AuthorProdItem[], total: 0, availableRoles: []});

      return forkJoin([pictures$, tunes$, prods$]).pipe(
        map(([pictures, tunes, prods]) => ({
          loading: false,
          pictures: pictures.items,
          tunes: tunes.items,
          prods: prods.items,
        })),
        startWith({loading: true, pictures: [], tunes: [], prods: []}),
      );
    }),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly picturesService: AuthorPicturesService,
    private readonly tunesService: AuthorTunesService,
    private readonly prodsService: AuthorProdsApiService,
  ) {}

  setContext(elementId: number, tabs: AuthorTabsDto): void {
    this.contextStore.next({elementId, tabs});
  }
}
