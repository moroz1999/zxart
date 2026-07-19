import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {PartyDto} from '../../shared/models/party-dto';
import {ZxGridComponent} from '../../shared/ui/zx-grid/zx-grid.component';
import {ZxPartyCardComponent} from '../zx-party-card/zx-party-card.component';

@Component({
  selector: 'zx-parties-list',
  standalone: true,
  imports: [CommonModule, ZxGridComponent, ZxPartyCardComponent],
  templateUrl: './zx-parties-list.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartiesListComponent {
  @Input() parties: PartyDto[] = [];

  trackById(_index: number, party: PartyDto): number {
    return party.id;
  }
}
