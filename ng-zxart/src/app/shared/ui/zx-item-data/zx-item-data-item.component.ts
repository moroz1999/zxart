import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {ZxBodyDirective, ZxBodySmMutedDirective,} from '../../directives/typography/typography.directives';

@Component({
  selector: 'zx-item-data-item',
  standalone: true,
  imports: [
    ZxBodyDirective,
    ZxBodySmMutedDirective,
  ],
  templateUrl: './zx-item-data-item.component.html',
  styleUrls: ['./zx-item-data-item.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxItemDataItemComponent {
  @Input({required: true}) label!: string;
}
