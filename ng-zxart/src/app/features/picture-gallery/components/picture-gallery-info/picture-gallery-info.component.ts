import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Lightbox} from 'ng-gallery/lightbox';
import {PictureGalleryInfo} from '../../models/picture-gallery-info';
import {AuthorDto} from '../../../../shared/models/author-dto';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {ZxBadgeComponent} from '../../../../shared/ui/zx-badge/zx-badge.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxItemControlsComponent} from '../../../../shared/ui/zx-item-controls/zx-item-controls.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';

/** Info block of a gallery slide: the picture's title, authors, format and vote controls. */
@Component({
  selector: 'zx-picture-gallery-info',
  standalone: true,
  imports: [
    CommonModule,
    RouterLink,
    TranslateModule,
    TextDirective,
    ZxBadgeComponent,
    ZxInlineComponent,
    ZxItemControlsComponent,
    ZxStackComponent,
  ],
  templateUrl: './picture-gallery-info.component.html',
  styleUrls: ['./picture-gallery-info.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class PictureGalleryInfoComponent {
  @Input({required: true}) info!: PictureGalleryInfo;

  constructor(private readonly lightbox: Lightbox) {}

  /** A link leaves the gallery, so it closes before the page changes under it. */
  closeGallery(): void {
    this.lightbox.close();
  }

  trackByUrl(_index: number, author: AuthorDto): string {
    return author.url;
  }
}
