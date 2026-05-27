import {ChangeDetectionStrategy, Component, HostBinding, Input} from '@angular/core';
import {NgIf} from '@angular/common';
import {ZxMedalComponent, ZxMedalVariant} from '../../ui/zx-medal/zx-medal.component';
import {TextDirective} from '../../ui/typography/directives/text.directive';

@Component({
  selector: 'zx-party-place',
  standalone: true,
  imports: [NgIf, ZxMedalComponent, TextDirective],
  templateUrl: './zx-party-place.component.html',
  styleUrl: './zx-party-place.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPartyPlaceComponent {
  @Input() place: number | null | undefined = null;

  @HostBinding('attr.hidden')
  get hidden(): '' | null {
    return this.hasPlace ? null : '';
  }

  get hasPlace(): boolean {
    return typeof this.place === 'number' && this.place > 0;
  }

  get medalVariant(): ZxMedalVariant {
    switch (this.place) {
      case 1:
        return 'gold';
      case 2:
        return 'silver';
      case 3:
        return 'bronze';
      default:
        return 'outlined';
    }
  }
}
