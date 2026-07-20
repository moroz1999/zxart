import {ChangeDetectionStrategy, Component, Input, OnChanges, SimpleChanges} from '@angular/core';
import {CommonModule} from '@angular/common';
import {RouterLink} from '@angular/router';
import {TranslateModule} from '@ngx-translate/core';
import {Observable, of} from 'rxjs';
import {shareReplay} from 'rxjs/operators';
import {PressDetailsDto, PressMentionDto} from '../../models/press-details.dto';
import {PressDetailsApiService} from '../../services/press-details-api.service';
import {ZxPressEditingControlsComponent} from '../zx-press-editing-controls/zx-press-editing-controls.component';
import {ZxSpinnerComponent} from '../../../../shared/ui/zx-spinner/zx-spinner.component';
interface MentionGroup {
  labelKey: string;
  items: PressMentionDto[];
}

/** Read view for `press/:id`. */
@Component({
  selector: 'zx-press-details-view',
  standalone: true,
  imports: [CommonModule, RouterLink, TranslateModule, ZxPressEditingControlsComponent, ZxSpinnerComponent],
  templateUrl: './zx-press-details.component.html',
  styleUrls: ['./zx-press-details.component.scss'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ZxPressDetailsComponent implements OnChanges {
  @Input() elementId = 0;

  details$: Observable<PressDetailsDto | null> = of(null);

  constructor(private readonly api: PressDetailsApiService) {}

  ngOnChanges(changes: SimpleChanges): void {
    if (!changes['elementId']) {
      return;
    }
    if (!this.elementId || +this.elementId <= 0) {
      this.details$ = of(null);
      return;
    }
    this.details$ = this.api.getDetails(+this.elementId).pipe(shareReplay(1));
  }

  mentionGroups(details: PressDetailsDto): MentionGroup[] {
    return [
      {labelKey: 'press-details.authors', items: details.authors},
      {labelKey: 'press-details.people', items: details.people},
      {labelKey: 'press-details.groups', items: details.groups},
      {labelKey: 'press-details.software', items: details.software},
      {labelKey: 'press-details.pictures', items: details.pictures},
      {labelKey: 'press-details.tunes', items: details.tunes},
      {labelKey: 'press-details.parties', items: details.parties},
    ].filter(group => group.items.length > 0);
  }

  trackByMention(_index: number, item: PressMentionDto): number {
    return item.id;
  }

  trackByGroup(_index: number, group: MentionGroup): string {
    return group.labelKey;
  }
}
