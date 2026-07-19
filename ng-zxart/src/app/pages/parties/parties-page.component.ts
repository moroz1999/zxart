import {CommonModule} from '@angular/common';
import {HttpClient} from '@angular/common/http';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {catchError, combineLatest, map, Observable, of, shareReplay} from 'rxjs';
import {PartyDto} from '../../shared/models/party-dto';
import {ZxPartiesListComponent} from '../../entities/zx-parties-list/zx-parties-list.component';

/** Routed collection page for `parties` (and `parties/:year`) — party cards. */
@Component({
  selector: 'zx-parties-page',
  standalone: true,
  imports: [CommonModule, ZxPartiesListComponent],
  templateUrl: './parties-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PartiesPageComponent {
  private readonly all$: Observable<PartyDto[]> = this.http
    .get<{parties: PartyDto[]}>('/parties-data/')
    .pipe(
      map(response => response?.parties ?? []),
      catchError(() => of([])),
      shareReplay({bufferSize: 1, refCount: false}),
    );

  readonly parties$: Observable<PartyDto[]> = combineLatest([this.all$, this.route.paramMap]).pipe(
    map(([parties, params]) => {
      const year = params.get('year');
      return year ? parties.filter(party => party.year === year) : parties;
    }),
  );

  constructor(
    private readonly http: HttpClient,
    private readonly route: ActivatedRoute,
  ) {}
}
