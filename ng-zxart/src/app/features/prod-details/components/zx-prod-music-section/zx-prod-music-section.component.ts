import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {ZxSkeletonComponent} from '../../../../shared/ui/zx-skeleton/zx-skeleton.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxMusicListComponent} from '../../../music-list/components/zx-music-list/zx-music-list.component';

@Component({
  selector: 'zx-prod-music-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxSkeletonComponent,
    ZxHeading2Directive,
    ZxMusicListComponent,
  ],
  templateUrl: './zx-prod-music-section.component.html',
  styleUrls: ['./zx-prod-music-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdMusicSectionComponent {
  @Input({required: true}) elementId!: number;

  mounted = false;

  constructor(private readonly cdr: ChangeDetectorRef) {}

  onInViewport(): void {
    if (this.mounted) {
      return;
    }
    this.mounted = true;
    this.cdr.markForCheck();
  }
}
