import {ChangeDetectionStrategy, Component, EventEmitter, Input, Output} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TextDirective} from '../typography/directives/text.directive';
import {ProdReleaseInlayDto} from '../../../features/prod-details/models/prod-release-inlay.dto';
import {ProdReleaseLabelPipe} from '../../../features/prod-details/pipes/prod-release-label.pipe';


import {RouterLink} from '@angular/router';@Component({
  selector: 'zx-inlay-tile',
  standalone: true,
  imports: [RouterLink, CommonModule, TextDirective, ProdReleaseLabelPipe],
  templateUrl: './zx-inlay-tile.component.html',
  styleUrl: './zx-inlay-tile.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxInlayTileComponent {
  @Input({required: true}) inlay!: Pick<ProdReleaseInlayDto, 'title' | 'imageUrl'>;
  @Input() releaseInfo: ProdReleaseInlayDto | null = null;
  @Input() caption: string | null = null;
  @Output() imageClick = new EventEmitter<void>();
}
