import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxMusicListComponent} from '../../../music-list/components/zx-music-list/zx-music-list.component';

@Component({
  selector: 'zx-prod-music-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxHeading2Directive,
    ZxMusicListComponent,
  ],
  templateUrl: './zx-prod-music-section.component.html',
  styleUrls: ['./zx-prod-music-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdMusicSectionComponent {
  @Input({required: true}) elementId!: number;
}
