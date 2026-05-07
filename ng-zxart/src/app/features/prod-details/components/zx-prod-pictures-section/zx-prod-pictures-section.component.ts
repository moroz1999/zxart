import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input, OnDestroy} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxPictureGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-picture-grid-skeleton/zx-picture-grid-skeleton.component';
import {ZxHeading2Directive} from '../../../../shared/directives/typography/typography.directives';
import {ZxPicturesListComponent} from '../../../picture-list/components/zx-pictures-list/zx-pictures-list.component';
import {ZxPictureDto} from '../../../../shared/models/zx-picture-dto';
import {PictureListService} from '../../../picture-list/services/picture-list.service';
import {Subscription} from 'rxjs';

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
export class ZxProdPicturesSectionComponent implements OnDestroy {
  @Input({required: true}) elementId!: number;

  mounted = false;
  loading = false;
  error = false;
  pictures: ZxPictureDto[] = [];

  private readonly subscription = new Subscription();

  @HostBinding('style.display')
  get display(): string {
    return this.mounted && !this.loading && !this.error && this.pictures.length === 0 ? 'none' : 'block';
  }

  constructor(
    private readonly pictureListService: PictureListService,
    private readonly cdr: ChangeDetectorRef,
  ) {}

  onInViewport(): void {
    if (this.mounted) {
      return;
    }
    this.mounted = true;
    this.loading = true;
    this.error = false;
    this.subscription.add(
      this.pictureListService.getPictures(this.elementId).subscribe({
        next: pictures => {
          this.loading = false;
          this.pictures = pictures;
          this.cdr.markForCheck();
        },
        error: () => {
          this.loading = false;
          this.error = true;
          this.cdr.markForCheck();
        },
      }),
    );
    this.cdr.markForCheck();
  }

  ngOnDestroy(): void {
    this.subscription.unsubscribe();
  }
}
