import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {PartyDto} from '../../models/party-dto';
import {TextDirective} from '../typography/directives/text.directive';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

@Component({
  selector: 'zx-party-card',
  standalone: true,
  imports: [CommonModule, TextDirective, ZxPanelComponent],
  templateUrl: './zx-party-card.component.html',
  styleUrls: ['./zx-party-card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyCardComponent {
  @Input() party!: PartyDto;
}
