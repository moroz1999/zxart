import {Component, Input, OnInit} from '@angular/core';
import {AsyncPipe, CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {ZxPictureDto} from '../../models/zx-picture-dto';
import {ZxPanelComponent} from '../zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../zx-badge/zx-badge.component';
import {ZxCaptionDirective} from '../../directives/typography/typography.directives';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ZxItemControlsComponent} from '../zx-item-controls/zx-item-controls.component';
import {PictureSettingsService} from '../../../features/picture-settings/services/picture-settings.service';
import {PictureUrlBuilderService} from '../../services/picture-url-builder.service';

@Component({
  selector: 'zx-picture-card',
  standalone: true,
  imports: [
    CommonModule,
    AsyncPipe,
    TranslateModule,
    ZxPanelComponent,
    ZxBadgeComponent,
    ZxCaptionDirective,
    LightboxModule,
    ZxItemControlsComponent,
  ],
  templateUrl: './zx-picture-card.component.html',
  styleUrls: ['./zx-picture-card.component.scss']
})
export class ZxPictureCardComponent implements OnInit {
  @Input() picture!: ZxPictureDto;
  @Input() galleryIndex: number | null = null;
  @Input() galleryId: string | null = null;

  readonly defaultGalleryId = 'zx-picture-lightbox-default';

  imageUrl$!: Observable<string>;

  constructor(
    private pictureSettingsService: PictureSettingsService,
    private pictureUrlBuilderService: PictureUrlBuilderService,
  ) {}

  ngOnInit(): void {
    this.imageUrl$ = this.pictureSettingsService.settings.pipe(
      map(settings => this.pictureUrlBuilderService.buildUrl(this.picture, settings, 1)),
    );
  }

  get medalClass(): string | null {
    if (!this.picture.party?.place) return null;
    switch (this.picture.party.place) {
      case 1: return 'medal-gold';
      case 2: return 'medal-silver';
      case 3: return 'medal-bronze';
      default: return null;
    }
  }
}
