import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {TextDirective} from '../typography/directives/text.directive';
import {ProdReleaseInlayDto} from '../../../features/prod-details/models/prod-release-inlay.dto';
import {ProdReleaseLabelPipe} from '../../../features/prod-details/pipes/prod-release-label.pipe';

@Component({
  selector: 'zx-inlay-tile',
  standalone: true,
  imports: [CommonModule, TranslateModule, TextDirective, ProdReleaseLabelPipe],
  templateUrl: './zx-inlay-tile.component.html',
  styleUrl: './zx-inlay-tile.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxInlayTileComponent {
  @Input({required: true}) inlay!: ProdReleaseInlayDto;
  @Input() showReleaseInfo = false;
  @Output() imageClick = new EventEmitter<void>();
}
