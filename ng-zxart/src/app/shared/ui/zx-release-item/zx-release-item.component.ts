import {Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ZxReleaseDto} from '../../models/zx-release-dto';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';

@Component({
  selector: 'zx-release-item',
  standalone: true,
  imports: [CommonModule, ZxCaptionDirective],
  templateUrl: './zx-release-item.component.html',
  styleUrls: ['./zx-release-item.component.scss']
})
export class ZxReleaseItemComponent {
  @Input() release!: ZxReleaseDto;
}
