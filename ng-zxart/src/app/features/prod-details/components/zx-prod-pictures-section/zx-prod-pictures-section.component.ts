import {ChangeDetectionStrategy, ChangeDetectorRef, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxPictureGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxPicturesListComponent,} from '../../../picture-list/components/zx-pictures-list/zx-pictures-list.component';

@Component({
  selector: 'zx-prod-pictures-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxPictureGridSkeletonComponent,
    ZxHeading2Directive,
    ZxPicturesListComponent,
  ],
  templateUrl: './zx-prod-pictures-section.component.html',
  styleUrls: ['./zx-prod-pictures-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxProdPicturesSectionComponent {
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
