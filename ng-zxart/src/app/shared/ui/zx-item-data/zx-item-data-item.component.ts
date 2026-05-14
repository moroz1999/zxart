import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {TextDirective} from '../typography/directives/text.directive';

@Component({
  selector: 'zx-item-data-item',
  standalone: true,
  imports: [
    TextDirective,
  ],
  templateUrl: './zx-item-data-item.component.html',
  styleUrls: ['./zx-item-data-item.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxItemDataItemComponent {
  @Input({required: true}) label!: string;
}
