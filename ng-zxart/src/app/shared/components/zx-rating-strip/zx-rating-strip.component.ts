import {ChangeDetectionStrategy, Component, Input, OnInit} from '@angular/core';
import {NgFor, NgIf} from '@angular/common';
import {TranslateModule} from '@ngx-translate/core';
import {SvgIconComponent, SvgIconRegistryService} from 'angular-svg-icon';
import {environment} from '../../../../environments/environment';
import {TextDirective} from '../../ui/typography/directives/text.directive';
import {HeadingDirective} from '../../ui/typography/directives/heading.directive';
import {ZxCalloutComponent} from '../../ui/zx-callout/zx-callout.component';

export type RatingStripType = 'artist' | 'musician' | 'overall';

export interface RatingStripItem {
  type: RatingStripType;
  value: number;
}

const TYPE_ICONS: Record<RatingStripType, string> = {
  artist: 'image',
  musician: 'music-note',
  overall: 'star',
};

/**
 * Hero "final rating" strip: a labelled, accent-bordered row of average ratings.
 * Reused across author, tune, picture and release heroes; the rating kind is
 * configured per item via `type`, which selects the icon and the sub-label.
 */
@Component({
  selector: 'zx-rating-strip',
  standalone: true,
  imports: [NgFor, NgIf, TranslateModule, SvgIconComponent, TextDirective, HeadingDirective, ZxCalloutComponent],
  templateUrl: './zx-rating-strip.component.html',
  styleUrl: './zx-rating-strip.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxRatingStripComponent implements OnInit {
  @Input() label = '';
  @Input() items: RatingStripItem[] = [];
  /** Show the per-item type sub-label (e.g. "artist"). Useful to disambiguate
   *  multiple ratings (author hero); hidden when a single rating is unambiguous. */
  @Input() showTypeLabel = true;

  constructor(private readonly iconReg: SvgIconRegistryService) {}

  ngOnInit(): void {
    this.iconReg.loadSvg(`${environment.svgUrl}image.svg`, 'image')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}music-note.svg`, 'music-note')?.subscribe();
    this.iconReg.loadSvg(`${environment.svgUrl}star.svg`, 'star')?.subscribe();
  }

  get visibleItems(): RatingStripItem[] {
    return this.items.filter(item => item.value > 0);
  }

  iconFor(type: RatingStripType): string {
    return TYPE_ICONS[type];
  }
}
