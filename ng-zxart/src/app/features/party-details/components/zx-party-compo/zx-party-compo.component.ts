import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {BehaviorSubject, combineLatest, Observable, of} from 'rxjs';
import {map, startWith} from 'rxjs/operators';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {ZxTuneDto} from '../../../../shared/models/zx-tune-dto';
import {ZxProd} from '../../../../shared/models/zx-prod';
import {PartyCompoDto, PartyCompoMedium, PartyCoreDto} from '../../models/party-core.dto';
import {PartyWorksService} from '../../services/party-works.service';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxButtonControlsComponent} from '../../../../shared/ui/zx-button-controls/zx-button-controls.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxProdsListComponent} from '../../../../entities/zx-prods-list/zx-prods-list.component';
import {ZxPicturesListComponent} from '../../../picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxMusicListComponent} from '../../../music-list/components/zx-music-list/zx-music-list.component';
import {ZxProdsListSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-prods-list-skeleton/zx-prods-list-skeleton.component';
import {ZxPictureGridSkeletonComponent} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';

type PartyCompoSortKey = 'place' | 'rating' | 'views' | 'listens';

interface PartyCompoSortOption {
  readonly key: PartyCompoSortKey;
  readonly labelKey: string;
}

interface Works {
  readonly prods: ZxProd[];
  readonly pictures: ZxPictureDto[];
  readonly tunes: ZxTuneDto[];
}

interface PartyCompoVm extends Works {
  readonly loading: boolean;
}

const EMPTY_WORKS: Works = {prods: [], pictures: [], tunes: []};

const SORT_OPTIONS: Record<PartyCompoMedium, PartyCompoSortOption[]> = {
  prod: [
    {key: 'place', labelKey: 'party-details.compo.sort-place'},
    {key: 'rating', labelKey: 'party-details.compo.sort-rating'},
  ],
  picture: [
    {key: 'place', labelKey: 'party-details.compo.sort-place'},
    {key: 'rating', labelKey: 'party-details.compo.sort-rating'},
    {key: 'views', labelKey: 'party-details.compo.sort-views'},
  ],
  music: [
    {key: 'place', labelKey: 'party-details.compo.sort-place'},
    {key: 'rating', labelKey: 'party-details.compo.sort-rating'},
    {key: 'listens', labelKey: 'party-details.compo.sort-listens'},
  ],
};

const NO_PLACE = Number.MAX_SAFE_INTEGER;

@Component({
  selector: 'zx-party-compo',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxStackComponent,
    ZxInlineComponent,
    ZxButtonControlsComponent,
    ZxButtonComponent,
    TextDirective,
    ZxProdsListComponent,
    ZxPicturesListComponent,
    ZxMusicListComponent,
    ZxProdsListSkeletonComponent,
    ZxPictureGridSkeletonComponent,
  ],
  templateUrl: './zx-party-compo.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyCompoComponent implements OnInit {
  @Input() core!: PartyCoreDto;
  @Input() compo!: PartyCompoDto;

  activeSort: PartyCompoSortKey = 'place';
  sortOptions: PartyCompoSortOption[] = [];
  vm$: Observable<PartyCompoVm> = of({loading: true, ...EMPTY_WORKS});

  private readonly sort$ = new BehaviorSubject<PartyCompoSortKey>('place');

  constructor(private readonly partyWorks: PartyWorksService) {}

  ngOnInit(): void {
    this.sortOptions = SORT_OPTIONS[this.compo.medium];
    this.vm$ = combineLatest([this.loadWorks(), this.sort$]).pipe(
      map(([works, sort]) => ({loading: false, ...this.applySort(works, sort)})),
      startWith({loading: true, ...EMPTY_WORKS}),
    );
  }

  setSort(key: PartyCompoSortKey): void {
    this.activeSort = key;
    this.sort$.next(key);
  }

  private loadWorks(): Observable<Works> {
    const partyId = this.core.id;
    switch (this.compo.medium) {
      case 'prod':
        return this.partyWorks.getCompoProds(partyId, this.compo.compoType).pipe(map(prods => ({...EMPTY_WORKS, prods})));
      case 'picture':
        return this.partyWorks.getCompoPictures(partyId, this.compo.compoType).pipe(map(pictures => ({...EMPTY_WORKS, pictures})));
      case 'music':
        return this.partyWorks.getCompoTunes(partyId, this.compo.compoType).pipe(map(tunes => ({...EMPTY_WORKS, tunes})));
      default:
        return of(EMPTY_WORKS);
    }
  }

  private applySort(works: Works, sort: PartyCompoSortKey): Works {
    switch (this.compo.medium) {
      case 'prod':
        return {...works, prods: [...works.prods].sort((a, b) => sort === 'rating'
          ? b.votes - a.votes
          : ZxPartyCompoComponent.place(a.partyPlace) - ZxPartyCompoComponent.place(b.partyPlace))};
      case 'picture':
        return {...works, pictures: [...works.pictures].sort((a, b) => {
          if (sort === 'rating') return b.votes - a.votes;
          if (sort === 'views') return b.views - a.views;
          return ZxPartyCompoComponent.place(a.party?.place) - ZxPartyCompoComponent.place(b.party?.place);
        })};
      case 'music':
        return {...works, tunes: [...works.tunes].sort((a, b) => {
          if (sort === 'rating') return b.votes - a.votes;
          if (sort === 'listens') return b.plays - a.plays;
          return ZxPartyCompoComponent.place(a.party?.place) - ZxPartyCompoComponent.place(b.party?.place);
        })};
      default:
        return works;
    }
  }

  private static place(value: number | null | undefined): number {
    return value === null || value === undefined || value <= 0 ? NO_PLACE : value;
  }
}
