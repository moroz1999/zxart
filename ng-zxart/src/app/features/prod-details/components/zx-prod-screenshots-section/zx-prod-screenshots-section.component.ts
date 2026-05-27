import {animate, group, query, stagger, style, transition, trigger} from '@angular/animations';
import {ChangeDetectionStrategy, ChangeDetectorRef, Component, HostBinding, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {forkJoin} from 'rxjs';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../../environments/environment';
import {InViewportDirective} from '../../../../shared/directives/in-viewport.directive';
import {
  ZxScreenshotGridSkeletonComponent
} from '../../../../shared/ui/zx-skeleton/components/zx-screenshot-grid-skeleton/zx-screenshot-grid-skeleton.component';
import {
  PictureGalleryHostComponent,
} from '../../../picture-gallery/components/picture-gallery-host/picture-gallery-host.component';
import {PictureGalleryService} from '../../../picture-gallery/services/picture-gallery.service';
import {PictureGalleryItem} from '../../../picture-gallery/models/picture-gallery-item';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ProdScreenshotsApiService} from '../../services/prod-screenshots-api.service';
import {ScreenshotMoveApiService} from '../../services/screenshot-move-api.service';
import {ProdFileDto} from '../../models/prod-file.dto';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxButtonComponent} from '../../../../shared/ui/zx-button/zx-button.component';
import {ElementPrivilegesApiService} from '../../../../shared/services/element-privileges-api.service';

@Component({
  selector: 'zx-prod-screenshots-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    InViewportDirective,
    ZxScreenshotGridSkeletonComponent,
    PictureGalleryHostComponent,
    LightboxModule,
    TextDirective,
    HeadingDirective,
    ZxStackComponent,
    ZxButtonComponent,
    SvgIconComponent,
  ],
  templateUrl: './zx-prod-screenshots-section.component.html',
  styleUrls: ['./zx-prod-screenshots-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
  animations: [
    trigger('fileEnter', [
      transition(':enter', [
        style({ opacity: 0, transform: 'translateY(8px)' }),
        animate('200ms ease-out', style({ opacity: 1, transform: 'translateY(0)' })),
      ]),
    ]),
    trigger('expandSection', [
      transition(':enter', [
        style({ height: '0', overflow: 'hidden' }),
        group([
          animate('350ms ease-out', style({ height: '*' })),
          query('.prod-screenshots-section__item', [
            style({ opacity: 0, transform: 'translateY(8px)' }),
            stagger(40, animate('200ms ease-out', style({ opacity: 1, transform: 'translateY(0)' }))),
          ], { optional: true }),
        ]),
      ]),
    ]),
  ],
})
export class ZxProdScreenshotsSectionComponent {
  @Input({required: true}) elementId!: number;
  @Input() titleKey = 'prod-details.screenshots';

  readonly maxVisible = 6;
  loading = false;
  loaded = false;
  files: ProdFileDto[] = [];
  galleryId = '';
  showAll = false;
  canReorder = false;
  moving = false;

  get visibleFiles(): ProdFileDto[] {
    if (this.showAll && this.files.length > this.maxVisible) {
      return this.files.slice(0, this.maxVisible + 1);
    }
    return this.files.slice(0, this.maxVisible);
  }

  get extraFiles(): ProdFileDto[] {
    return this.files.slice(this.maxVisible + 1);
  }

  get moreCount(): number {
    return this.showAll ? 0 : Math.max(0, this.files.length - this.maxVisible);
  }

  expandAll(): void {
    this.showAll = true;
    this.cdr.markForCheck();
  }

  @HostBinding('style.display')
  get display(): string {
    return this.loaded && this.files.length === 0 ? 'none' : '';
  }

  constructor(
    private readonly api: ProdScreenshotsApiService,
    private readonly moveApi: ScreenshotMoveApiService,
    private readonly elementPrivilegesApi: ElementPrivilegesApiService,
    private readonly gallery: PictureGalleryService,
    private readonly cdr: ChangeDetectorRef,
    private readonly iconReg: SvgIconRegistryService,
  ) {
    this.iconReg.loadSvg(`${environment.svgUrl}skip-previous.svg`, 'skip-previous')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}skip-next.svg`, 'skip-next')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}download.svg`, 'download')?.subscribe();
  }

  onInViewport(): void {
    if (this.loaded || this.loading) {
      return;
    }
    this.galleryId = `zx-prod-screenshots-${this.elementId}`;
    this.loading = true;
    forkJoin({
      files: this.api.getScreenshots(this.elementId),
      privileges: this.elementPrivilegesApi.getPrivileges(this.elementId, ['publicReceive']),
    }).subscribe(({files, privileges}) => {
      this.files = files;
      this.canReorder = privileges['publicReceive'] === true;
      this.loaded = true;
      this.loading = false;
      if (files.length) {
        this.gallery.loadItems(this.galleryId, files.map(this.toGalleryItem));
      }
      this.cdr.markForCheck();
    });
  }

  onMove(file: ProdFileDto, direction: 'left' | 'right'): void {
    if (this.moving) {
      return;
    }
    this.moving = true;
    this.cdr.markForCheck();
    this.moveApi.move(this.elementId, file.id, direction).subscribe(files => {
      this.moving = false;
      if (files !== null) {
        this.files = files;
        if (files.length) {
          this.gallery.loadItems(this.galleryId, files.map(this.toGalleryItem));
        }
      }
      this.cdr.markForCheck();
    });
  }

  trackByFileId(_index: number, file: ProdFileDto): number {
    return file.id;
  }

  isFirst(file: ProdFileDto): boolean {
    return this.files[0]?.id === file.id;
  }

  isLast(file: ProdFileDto): boolean {
    return this.files[this.files.length - 1]?.id === file.id;
  }

  private toGalleryItem(file: ProdFileDto): PictureGalleryItem {
    return {
      id: file.id,
      title: file.title,
      thumbUrl: file.imageUrl ?? file.fullImageUrl ?? '',
      largeUrl: file.fullImageUrl ?? file.imageUrl ?? '',
      detailsUrl: file.downloadUrl,
    };
  }
}
