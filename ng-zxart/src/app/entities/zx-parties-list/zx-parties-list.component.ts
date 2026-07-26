import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {PartyDto} from '../../shared/models/party-dto';
import {ZxGridComponent} from '../../shared/ui/zx-grid/zx-grid.component';
import {ZxTableComponent} from '../../shared/ui/zx-table/zx-table.component';
import {ZxPartyCardComponent} from '../zx-party-card/zx-party-card.component';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';

export type PartyListViewMode = 'cards' | 'table';

@Component({
  selector: 'zx-parties-list',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule, ZxGridComponent, ZxTableComponent, ZxPartyCardComponent],
  templateUrl: './zx-parties-list.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartiesListComponent {
  @Input() parties: PartyDto[] = [];
  @Input() viewMode: PartyListViewMode = 'cards';

  trackById(_index: number, party: PartyDto): number {
    return party.id;
  }
}
