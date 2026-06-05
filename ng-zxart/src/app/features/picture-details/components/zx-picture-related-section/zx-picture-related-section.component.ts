import {ChangeDetectionStrategy, Component, Input} from '@angular/core';
import {CommonModule} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {PictureRelatedRailDto, PictureRelatedRailKind} from '../../models/picture-details.dto';
import {ZxPanelComponent} from '../../../../shared/ui/zx-panel/zx-panel.component';
import {ZxGridComponent} from '../../../../shared/ui/zx-grid/zx-grid.component';
import {ZxStackComponent} from '../../../../shared/ui/zx-stack/zx-stack.component';
import {ZxInlineComponent} from '../../../../shared/ui/zx-inline/zx-inline.component';
import {ZxPartyPlaceComponent} from '../../../../shared/lib/zx-party-place/zx-party-place.component';
import {TextDirective} from '../../../../shared/ui/typography/directives/text.directive';
import {HeadingDirective} from '../../../../shared/ui/typography/directives/heading.directive';

@Component({
  selector: 'zx-picture-related-section',
  standalone: true,
  imports: [
    CommonModule,
    TranslateModule,
    ZxPanelComponent,
    ZxGridComponent,
    ZxStackComponent,
    ZxInlineComponent,
    ZxPartyPlaceComponent,
    TextDirective,
    HeadingDirective,
  ],
  templateUrl: './zx-picture-related-section.component.html',
  styleUrls: ['./zx-picture-related-section.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPictureRelatedSectionComponent {
  @Input({required: true}) rails: PictureRelatedRailDto[] = [];

  private readonly titleKeys: Record<PictureRelatedRailKind, string> = {
    prod: 'picture-details.related-prod',
    author: 'picture-details.related-author',
    tags: 'picture-details.related-tags',
  };

  railTitleKey(kind: PictureRelatedRailKind): string {
    return this.titleKeys[kind] ?? 'picture-details.related-prod';
  }

  authorNames(authors: {name: string}[]): string {
    return authors.map(author => author.name).join(', ');
  }
}
