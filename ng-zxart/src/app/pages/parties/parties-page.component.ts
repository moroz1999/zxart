import {CommonModule} from '@angular/common';
import {HttpClient} from '@angular/common/http';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {TranslateModule, TranslateService} from '@ngx-translate/core';
import {catchError, map, Observable, of, shareReplay, startWith, switchMap} from 'rxjs';
import {PartyDto} from '../../shared/models/party-dto';
import {
  PartyListViewMode,
  ZxPartiesListComponent,
} from '../../entities/zx-parties-list/zx-parties-list.component';
import {PARTY_YEARS} from '../../features/menu/menu.config';
import {ZxInlineComponent} from '../../shared/ui/zx-inline/zx-inline.component';
import {ZxNavChipsComponent} from '../../shared/ui/zx-nav-chips/zx-nav-chips.component';
import {ZxNavChip} from '../../shared/ui/zx-nav-chips/nav-chip';
import {ZxStackComponent} from '../../shared/ui/zx-stack/zx-stack.component';
import {ZxToggleComponent, ZxToggleOption} from '../../shared/ui/zx-toggle/zx-toggle.component';
import {HeadingDirective} from '../../shared/ui/typography/directives/heading.directive';
import {
  ZxPartiesListSkeletonComponent,
} from '../../entities/zx-parties-list-skeleton/zx-parties-list-skeleton.component';

interface PartiesPageVm {
  parties: PartyDto[] | null;
  selectedYear: string;
}

/** Routed collection page for `parties` (and `parties/:year`) — party cards. */
@Component({
  selector: 'zx-parties-page',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPartiesListComponent,
    ZxPartiesListSkeletonComponent,
    ZxNavChipsComponent,
    ZxInlineComponent,
    ZxStackComponent,
    ZxToggleComponent,
    HeadingDirective,
  ],
  templateUrl: './parties-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PartiesPageComponent {
  readonly years = PARTY_YEARS;
  readonly viewToggleOptions$: Observable<ZxToggleOption[]>;
  viewMode: PartyListViewMode = 'cards';

  readonly vm$: Observable<PartiesPageVm> = this.route.paramMap.pipe(
    switchMap(params => {
      const year = params.get('year');
      const url = year ? `/parties-data/?year=${encodeURIComponent(year)}` : '/parties-data/';
      return this.http.get<{parties: PartyDto[]}>(url).pipe(
        map(response => ({parties: response?.parties ?? [], selectedYear: year ?? ''})),
        catchError(() => of({parties: [], selectedYear: year ?? ''})),
        startWith({parties: null, selectedYear: year ?? ''}),
      );
    }),
    shareReplay({bufferSize: 1, refCount: false}),
  );

  constructor(
    private readonly http: HttpClient,
    private readonly route: ActivatedRoute,
    translateService: TranslateService,
  ) {
    this.viewToggleOptions$ = translateService.stream([
      'parties-page.view-cards',
      'parties-page.view-table',
    ]).pipe(
      map(translations => [
        {value: 'cards', label: translations['parties-page.view-cards'] as string},
        {value: 'table', label: translations['parties-page.view-table'] as string},
      ]),
    );
  }

  yearChips(selectedYear: string): ZxNavChip[] {
    return this.years.map(year => ({
      label: String(year),
      href: `/parties/${year}`,
      active: selectedYear === String(year),
    }));
  }

  setViewMode(value: string): void {
    if (value === 'cards' || value === 'table') {
      this.viewMode = value;
    }
  }
}
