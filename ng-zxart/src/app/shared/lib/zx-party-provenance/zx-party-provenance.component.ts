import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {NgIf} from '@angular/common';
import {RouterLink} from '@angular/router';
import {ZxPartyPlaceComponent} from '../zx-party-place/zx-party-place.component';
import {ZxInlineComponent} from '../../ui/zx-inline/zx-inline.component';
import {TextDirective} from '../../ui/typography/directives/text.directive';

/** Party appearance of a work: the party, the compo and the placement. */
export interface ZxPartyProvenanceDto {
  readonly title: string;
  readonly url: string;
  readonly place: number | null;
  readonly compoLabel: string | null;
}

/**
 * Provenance line for a work shown at a party: placement medal, party link and
 * compo. Rendered inside the hero provenance `zx-callout`.
 */
@Component({
  selector: 'zx-party-provenance',
  standalone: true,
  imports: [NgIf, RouterLink, ZxPartyPlaceComponent, ZxInlineComponent, TextDirective],
  templateUrl: './zx-party-provenance.component.html',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyProvenanceComponent {
  @Input({required: true}) party!: ZxPartyProvenanceDto;
}
