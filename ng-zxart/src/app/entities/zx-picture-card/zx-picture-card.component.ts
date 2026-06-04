import {ChangeDetectionStrategy, Component, HostBinding, Input, OnInit} from '@angular/core';
import {AsyncPipe, CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {Observable} from 'rxjs';
import {map} from 'rxjs/operators';
import {ZxPictureDto} from '../../shared/models/zx-picture-dto';
import {ZxPanelComponent} from '../../shared/ui/zx-panel/zx-panel.component';
import {ZxBadgeComponent} from '../../shared/ui/zx-badge/zx-badge.component';
import {TextDirective} from '../../shared/ui/typography/directives/text.directive';
import {LightboxModule} from 'ng-gallery/lightbox';
import {ZxItemControlsComponent} from '../../shared/ui/zx-item-controls/zx-item-controls.component';
import {PictureSettingsService} from '../../features/picture-settings/services/picture-settings.service';
import {PictureUrlBuilderService} from '../../shared/services/picture-url-builder.service';
import {ZxPartyPlaceComponent} from '../../shared/lib/zx-party-place/zx-party-place.component';

@Component({
  selector: 'zx-picture-card',
  standalone: true,
  imports: [
    CommonModule,
    AsyncPipe,
    TranslateModule,
    ZxPanelComponent,
    ZxBadgeComponent,
    TextDirective,
    LightboxModule,
    ZxItemControlsComponent,
    ZxPartyPlaceComponent,
  ],
  templateUrl: './zx-picture-card.component.html',
  styleUrls: ['./zx-picture-card.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
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

  @HostBinding('class.zx-picture-card--no-border')
  get noBorderClass(): boolean {
    return !this.pictureSettingsService.currentSettings.border;
  }

}
