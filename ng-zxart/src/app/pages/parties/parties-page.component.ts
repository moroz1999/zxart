import {CommonModule} from '@angular/common';
import {HttpClient} from '@angular/common/http';
import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {catchError, combineLatest, map, Observable, of} from 'rxjs';
import {PartyDto} from '../../shared/models/party-dto';
import {ZxPartyCardComponent} from '../../entities/zx-party-card/zx-party-card.component';

/** Routed collection page for `parties` (and `parties/:year`) — party cards. */
@Component({
  selector: 'zx-parties-page',
  standalone: true,
  imports: [CommonModule, ZxPartyCardComponent],
  templateUrl: './parties-page.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PartiesPageComponent {
  private readonly all$: Observable<PartyDto[]> = this.http
    .get<{parties: PartyDto[]}>('/parties-data/')
    .pipe(
      map(response => response?.parties ?? []),
      catchError(() => of([])),
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

  trackById(_index: number, party: PartyDto): number {
    return party.id;
  }
}
