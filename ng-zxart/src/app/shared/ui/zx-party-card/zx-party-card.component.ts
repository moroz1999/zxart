import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {PartyDto} from '../../models/party-dto';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';

@Component({
  selector: 'zx-party-card',
  standalone: true,
  imports: [CommonModule, ZxCaptionDirective, ZxPanelComponent],
  templateUrl: './zx-party-card.component.html',
  styleUrls: ['./zx-party-card.component.scss']
})
export class ZxPartyCardComponent {
  @Input() party!: PartyDto;
}
